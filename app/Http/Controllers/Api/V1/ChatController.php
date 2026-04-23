<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\MessageNotification;
use App\Traits\FormatsProfileData;

class ChatController extends Controller
{
    use FormatsProfileData;
    /**
     * List user's conversations.
     */
    /**
     * List user's conversations.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch conversations where user is involved and has at least one message
        $conversations = Conversation::with([
            'userOne.category', 'userOne.club', 'userOne.ownedClub',
            'userTwo.category', 'userTwo.club', 'userTwo.ownedClub'
        ])
            ->where(function ($query) use ($userId) {
                $query->where('user_one_id', $userId)
                    ->orWhere('user_two_id', $userId);
            })
            ->has('messages')
            ->latest('updated_at')
            ->get();

        // Collect all unique users to process them once
        $usersToProcess = collect();
        foreach ($conversations as $chat) {
            if ($chat->userOne) $usersToProcess->put($chat->userOne->id, $chat->userOne);
            if ($chat->userTwo) $usersToProcess->put($chat->userTwo->id, $chat->userTwo);
        }

        $currentUser = auth()->user();
        $usersToProcess->each(function ($user) use ($currentUser) {
            $profileData = $this->getProfileData($user, false, $currentUser);
            foreach ($profileData as $key => $value) {
                // Only set if not already flattened to avoid issues
                if (!is_array($user->{$key})) {
                    $user->{$key} = $value;
                }
            }
        });

        // Optional: Transform to unify "other_user" for frontend convenience
        $chats = $conversations->map(function ($chat) use ($userId) {
            $otherUser = ($chat->user_one_id == $userId) ? $chat->userTwo : $chat->userOne;

            // Calculate unread count for this conversation
            $unreadCount = $chat->messages()
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->count();

            return [
                'id' => $chat->id,
                'other_user' => $otherUser, // Frontend can just use this
                'last_message' => $chat->messages()->latest()->first(),
                'unread_count' => $unreadCount,
                'updated_at' => $chat->updated_at,
                'user_one' => $chat->userOne,
                'user_two' => $chat->userTwo,
                // 'service_request' => $chat->serviceRequest ?? null 
            ];
        });

        // Calculate total unread messages for the user
        $totalUnread = Message::whereHas('conversation', function ($q) use ($userId) {
            $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
        })
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'status' => true,
            'data' => $chats,
            'total_unread_count' => $totalUnread,
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

        // Mark messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = $conversation->messages()
            ->with([
                'sender.category',
                'sender.club',
                'sender.ownedClub'
            ])
            ->latest()
            ->paginate(20);

        // Unique senders processing
        $sendersToProcess = collect();
        foreach ($messages as $message) {
            if ($message->sender) $sendersToProcess->put($message->sender->id, $message->sender);
        }

        $sendersToProcess->each(function ($sender) use ($userId) {
            if (is_object($sender)) {
                $profileData = $this->getProfileData($sender, false, auth()->user());
                foreach ($profileData as $key => $value) {
                    if (!is_array($sender->{$key})) {
                        $sender->{$key} = $value;
                    }
                }
            }
        });

        $messages->getCollection()->transform(function ($message) {
            return $message;
        });

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
            'meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'body'      => $request->body,
            'meta'      => $request->meta,
        ]);

        $conversation->touch(); // Update updated_at

        // Enrich sender for response
        $message->load(['sender.category', 'sender.club', 'sender.ownedClub']);
        if ($message->sender) {
            $profileData = $this->getProfileData($message->sender, false, $request->user());
            foreach ($profileData as $key => $value) {
                $message->sender->{$key} = $value;
            }
        }

        // Notify Receiver
        try {
            $receiverId = ($conversation->user_one_id == $userId) ? $conversation->user_two_id : $conversation->user_one_id;
            $receiver = \App\Models\User::find($receiverId);
            if ($receiver) {
                $receiver->notify(new MessageNotification($message->load('sender')));
            }
        } catch (\Exception $e) {
            // Ignore
        }

        // Broadcast Event
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => true,
            'data' => $message->load('sender'),
            'message' => 'Message sent'
        ], 201);
    }
}
