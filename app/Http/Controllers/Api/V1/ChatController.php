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
    /**
     * List user's conversations.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch conversations where user is involved
        $conversations = Conversation::with(['userOne', 'userTwo'])
            ->where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->latest('updated_at')
            ->get();

        // Optional: Transform to unify "other_user" for frontend convenience
        $chats = $conversations->map(function ($chat) use ($userId) {
            $otherUser = $chat->user_one_id === $userId ? $chat->userOne : $chat->userTwo;
            return [
                'id' => $chat->id,
                'other_user' => $otherUser, // Frontend can just use this
                'last_message' => $chat->messages()->latest()->first(),
                'updated_at' => $chat->updated_at,
                // 'service_request' => $chat->serviceRequest ?? null // Optional, might be null
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $chats,
            'message' => 'Conversations retrieved'
        ]);
    }

    /**
     * Start or Get Conversation with a user.
     * POST /chat/conversations
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $authUser = $request->user()->id;
        $targetUser = $request->user_id;

        if ($authUser == $targetUser) {
            return response()->json(['status' => false, 'message' => 'Cannot chat with yourself'], 400);
        }

        // Check for existing conversation
        $conversation = Conversation::where(function ($q) use ($authUser, $targetUser) {
            $q->where('user_one_id', $authUser)->where('user_two_id', $targetUser);
        })->orWhere(function ($q) use ($authUser, $targetUser) {
            $q->where('user_one_id', $targetUser)->where('user_two_id', $authUser);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one_id' => $authUser,
                'user_two_id' => $targetUser,
                'service_request_id' => null
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $conversation,
            'message' => 'Conversation retrieved successfully'
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
