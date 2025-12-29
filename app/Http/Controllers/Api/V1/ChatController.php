<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * List user's conversations.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $conversations = Conversation::with(['userOne', 'userTwo', 'serviceRequest.service'])
            ->where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->latest('updated_at')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $conversations,
            'message' => 'Conversations retrieved'
        ]);
    }

    /**
     * Get messages for a conversation.
     */
    public function show(Request $request, $id)
    {
        $conversation = Conversation::find($id);

        if (!$conversation) {
            return response()->json(['status' => false, 'message' => 'Conversation not found'], 404);
        }

        // Authorization check
        $userId = $request->user()->id;
        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $messages = $conversation->messages()->with('sender')->latest()->paginate(20);

        return response()->json([
            'status' => true,
            'data' => $messages,
            'message' => 'Messages retrieved'
        ]);
    }

    /**
     * Send a message.
     */
    public function store(Request $request, $id)
    {
        $conversation = Conversation::find($id);

        if (!$conversation) {
            return response()->json(['status' => false, 'message' => 'Conversation not found'], 404);
        }

        $userId = $request->user()->id;
        if ($conversation->user_one_id !== $userId && $conversation->user_two_id !== $userId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        // Check if service is paid/active? (Optional logic as per user request: "then make a chat between them")
        // Since conversation is created after payment, we act as if it's allowed.

        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'body' => $request->body,
        ]);

        $conversation->touch(); // Update updated_at

        // Broadcast Event
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => true,
            'data' => $message->load('sender'),
            'message' => 'Message sent'
        ], 201);
    }
}
