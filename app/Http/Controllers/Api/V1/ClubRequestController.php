<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClubRequest;
use App\Models\User;
use App\Models\Club;
use Illuminate\Support\Facades\Validator;
use App\Notifications\ClubRequestNotification;

class ClubRequestController extends Controller
{
    /**
     * List my pending requests (Incoming to me).
     * If I am a user: invites from clubs.
     * If I am a club admin: join requests from users.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isClubAdmin = $user->ownedClub()->exists();
        $club = $user->ownedClub;

        // Default view: if user owns a club, show club view. Otherwise, user view.
        // User can explicitly pass 'view=user' or 'view=club'.
        $view = $request->get('view', $isClubAdmin ? 'club' : 'user');

        if ($view === 'club' && !$isClubAdmin) {
            return response()->json(['status' => false, 'message' => 'You do not own a club'], 403);
        }

        $query = ClubRequest::query();

        if ($view === 'club') {
            // Club context: handling requests related to the club I own
            if ($request->has('sent')) {
                // Invites my club sent to users
                $query->where('club_id', $club->id)->where('type', 'invite');
            } else {
                // Join requests users sent to my club
                $query->where('club_id', $club->id)->where('type', 'join');
            }
        } else {
            // User context: handling requests related to me personally
            if ($request->has('sent')) {
                // Join requests I sent to clubs
                $query->where('user_id', $user->id)->where('type', 'join');
            } else {
                // Club invites I received
                $query->where('user_id', $user->id)->where('type', 'invite');
            }
        }

        // Filter by status if provided, default pending
        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->with(['user', 'club'])->latest()->paginate(20);

        return response()->json(['status' => true, 'data' => $requests]);
    }

    /**
     * Create a request.
     * User -> join club
     * Club -> invite user
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $isClubAdmin = $user->ownedClub()->exists();
        $club = $user->ownedClub;

        $validator = Validator::make($request->all(), [
            'user_id' => 'required_if:type,invite|exists:users,id',
            'club_id' => 'required_if:type,join|exists:clubs,id',
            'type' => 'required|in:join,invite',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $type = $request->type;
        $targetUserId = $type === 'invite' ? $request->user_id : $user->id;
        $targetClubId = $type === 'join' ? $request->club_id : $club->id;

        // Validate permissions
        if ($type === 'invite' && (!$isClubAdmin || $club->id != $targetClubId)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized to invite'], 403);
        }
        if ($type === 'join' && $isClubAdmin) {
            // Club admin joining another club? Maybe allowed, but unusual.
            // For now assume user joining logic.
        }

        // Check existence
        $exists = ClubRequest::where('user_id', $targetUserId)
            ->where('club_id', $targetClubId)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($exists) {
            return response()->json(['status' => false, 'message' => 'Request already exists or user already member'], 400);
        }

        $req = ClubRequest::create([
            'user_id' => $targetUserId,
            'club_id' => $targetClubId,
            'type' => $type,
            'status' => 'pending'
        ]);

        // Notify Receiver
        try {
            $notifiable = ($type === 'invite') ? User::find($targetUserId) : Club::find($targetClubId)->owner;
            if ($notifiable) {
                $notifiable->notify(new ClubRequestNotification($req->load(['user', 'club']), [
                    'title' => ($type === 'invite') ? 'Club Invitation' : 'New Join Request',
                    'body' => ($type === 'invite') ? "You have been invited to join {$club->name}." : "{$user->name} requested to join your club.",
                    'push_title' => ($type === 'invite') ? ['en' => 'Club Invitation', 'ar' => 'دعوة من نادي'] : ['en' => 'New Join Request', 'ar' => 'طلب انضمام جديد'],
                    'push_body' => ($type === 'invite') 
                        ? ['en' => "You have been invited to join {$club->name}.", 'ar' => "تمت دعوتك للانضمام إلى {$club->name}."] 
                        : ['en' => "{$user->name} requested to join your club.", 'ar' => "طلب {$user->name} الانضمام إلى ناديك."]
                ]));
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return response()->json(['status' => true, 'message' => 'Request sent successfully', 'data' => $req]);
    }

    /**
     * Respond to a request (Accept/Reject).
     */
    public function respond(Request $request, $id)
    {
        $clubRequest = ClubRequest::find($id);
        if (!$clubRequest) {
            return response()->json(['status' => false, 'message' => 'Request not found'], 404);
        }

        $user = $request->user();
        $action = $request->input('action'); // 'accept' or 'reject'

        if (!in_array($action, ['accept', 'reject'])) {
            return response()->json(['status' => false, 'message' => 'Invalid action'], 400);
        }

        // Authorization check
        // If join request (User wants to join Club), Club Admin must respond.
        // If invite request (Club invites User), User must respond.

        $authorized = false;
        if ($clubRequest->type === 'join') {
            // Check if current user is the owner of the club
            if ($user->ownedClub && $user->ownedClub->id === $clubRequest->club_id) {
                $authorized = true;
            }
        } else { // invite
            // Check if current user is the target user
            if ($user->id === $clubRequest->user_id) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $status = ($action === 'accept') ? 'accepted' : 'rejected';
        $clubRequest->status = $status;
        $clubRequest->save();

        // Perform side effects on accept (Add user to club members logic)
        if ($status === 'accepted') {
            $member = User::find($clubRequest->user_id);
            $member->club_id = $clubRequest->club_id;
            $member->save();
        }

        // Notify User/Requester of status change
        try {
            // If join request -> Notify user who wanted to join
            // If invite request -> Notify club who sent the invite
            $notifiableUser = ($clubRequest->type === 'join') ? $user : User::find($clubRequest->user_id);
            // Wait, if it's an invite, the user responds, we notify the club owner?
            // Usually, we want to notify the other party.
            
            $otherParty = ($clubRequest->type === 'join') ? User::find($clubRequest->user_id) : $clubRequest->club->owner;
            
            if ($otherParty) {
                $otherParty->notify(new ClubRequestNotification($clubRequest->load(['user', 'club']), [
                    'title' => 'Club Request Update',
                    'body' => "The club request has been $status.",
                    'push_title' => ['en' => 'Club Request Update', 'ar' => 'تحديث طلب النادي'],
                    'push_body' => [
                        'en' => "The club request has been $status.",
                        'ar' => "تم $status طلب النادي."
                    ]
                ]));
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return response()->json(['status' => true, 'message' => "Request $status"]);
    }

    /**
     * Cancel/Delete a request.
     */
    public function destroy(Request $request, $id)
    {
        $clubRequest = ClubRequest::find($id);
        if (!$clubRequest) {
            return response()->json(['status' => false, 'message' => 'Request not found'], 404);
        }

        $user = $request->user();

        // Authorization:
        // Only the creator of the request can delete it while it's pending.
        $canDelete = false;

        if ($clubRequest->type === 'join' && $clubRequest->user_id === $user->id) {
            $canDelete = true;
        } elseif ($clubRequest->type === 'invite' && $user->ownedClub && $user->ownedClub->id === $clubRequest->club_id) {
            $canDelete = true;
        }

        if (!$canDelete) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($clubRequest->status !== 'pending') {
            return response()->json(['status' => false, 'message' => 'Cannot delete a processed request'], 400);
        }

        $clubRequest->delete();

        return response()->json(['status' => true, 'message' => 'Request cancelled successfully']);
    }
}
