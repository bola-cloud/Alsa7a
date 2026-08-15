<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    /**
     * Display a listing of the clubs.
     */
    public function index(Request $request)
    {
        // NOTE: the clubs table has no 'active' column — filtering on it made
        // this endpoint fail with SQLSTATE[42S22] on both V1 and V2.
        $clubs = Club::with(['sports', 'owner'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $clubs,
            'message' => 'Clubs retrieved successfully'
        ]);
    }

    /**
     * Display the specified club.
     */
    public function show($id)
    {
        // Direct access by id - country filter must not hide it.
        $club = Club::directAccess()->with(['sports', 'media', 'teams.sport', 'owner'])->find($id);

        if (!$club) {
            return response()->json(['status' => false, 'message' => 'Club not found'], 404);
        }

        // Fetch Users belonging to this club
        $members = \App\Models\User::where('club_id', $id)
            ->with(['category', 'media']) // Load category and media
            ->get()
            ->groupBy(function ($user) {
                $fallback = app()->getLocale() === 'ar' ? 'أخرى' : 'Other';
                return $user->category ? ($user->category->name ?: $fallback) : $fallback;
            });

        return response()->json([
            'status' => true,
            'data' => [
                'club' => $club,
                'roster' => $members, // $members is now a collection grouped by keys
            ],
            'message' => 'Club details retrieved successfully'
        ]);
    }

    /**
     * List members of a specific club.
     */
    public function getMembers(Request $request, $club_id)
    {
        $club = Club::find($club_id);
        if (!$club) {
            return response()->json(['status' => false, 'message' => 'Club not found'], 404);
        }

        $query = \App\Models\User::where('club_id', $club_id);

        // Filter by team_id
        if ($request->has('team_id') && $request->team_id !== null) {
            if ($request->team_id === 'none') {
                $query->whereNull('team_id');
            } else {
                $query->where('team_id', $request->team_id);
            }
        }

        // Search by name, email, or alsa7a_id
        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
                
                if (is_numeric($search)) {
                    $numericVal = (int) $search;
                    if ($numericVal > 100000 && ($numericVal - 100000) % 10 === 0) {
                        $realId = ($numericVal - 100000) / 10;
                        $q->orWhere('id', $realId);
                    }
                }
            });
        }

        $members = $query->with(['team', 'category'])->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $members,
            'message' => 'Club members retrieved successfully'
        ]);
    }

    /**
     * Update club member details (Team transfer, position, jersey number).
     */
    public function updateMember(Request $request, $club_id, $user_id)
    {
        $club = Club::find($club_id);
        if (!$club) {
            return response()->json(['status' => false, 'message' => 'Club not found'], 404);
        }

        // Authorization: Only the club owner can manage members
        if ($club->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized. Only the club owner can manage members.'], 403);
        }

        $member = \App\Models\User::where('club_id', $club_id)->where('id', $user_id)->first();
        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Member not found in this club'], 404);
        }

        // Safety Check: Cannot modify the club owner
        if ($member->id === $club->user_id) {
            return response()->json(['status' => false, 'message' => 'Cannot modify the club owner.'], 400);
        }

        // Safety Check: Cannot modify other club accounts
        if ($member->category && $member->category->isProtected()) {
            return response()->json(['status' => false, 'message' => 'Cannot modify a club account.'], 400);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'team_id' => 'nullable|exists:teams,id',
            'position' => 'nullable|string|max:100',
            'number' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // If team_id is provided, verify it belongs to the same club
        if ($request->filled('team_id')) {
            $team = \App\Models\Team::where('club_id', $club_id)->find($request->team_id);
            if (!$team) {
                return response()->json(['status' => false, 'message' => 'The selected team does not belong to this club.'], 422);
            }
            $member->team_id = $request->team_id;
        } elseif ($request->has('team_id')) {
            // Explicitly set to null (remove from team)
            $member->team_id = null;
        }

        if ($request->has('position')) {
            $member->position = $request->position;
        }

        if ($request->has('number')) {
            $member->number = $request->number;
        }

        $member->save();

        return response()->json([
            'status' => true,
            'data' => $member,
            'message' => 'Member updated successfully'
        ]);
    }

    /**
     * Remove a member from the club.
     */
    public function removeMember(Request $request, $club_id, $user_id)
    {
        $club = Club::find($club_id);
        if (!$club) {
            return response()->json(['status' => false, 'message' => 'Club not found'], 404);
        }

        // Authorization: Only the club owner can manage members
        if ($club->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized. Only the club owner can manage members.'], 403);
        }

        $member = \App\Models\User::where('club_id', $club_id)->where('id', $user_id)->first();
        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Member not found in this club'], 404);
        }

        // Safety Check: Cannot remove the club owner
        if ($member->id === $club->user_id) {
            return response()->json(['status' => false, 'message' => 'Cannot remove the club owner.'], 400);
        }

        // Safety Check: Cannot remove other club accounts
        if ($member->category && $member->category->isProtected()) {
            return response()->json(['status' => false, 'message' => 'Cannot remove a club account from the club roster.'], 400);
        }

        // Reset club and team association
        $member->club_id = null;
        $member->team_id = null;
        $member->position = null;
        $member->number = null;
        $member->save();

        return response()->json([
            'status' => true,
            'message' => 'Member removed from club successfully'
        ]);
    }
}
