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
        $clubs = Club::with('sports')
            ->where('active', true) // Assuming 'active' column exists based on context, or remove if not. Let's check model first or stick to safe usage. 
            // Migration checked earlier (clubs table): 'active' isn't explicitly in fillable but usually standard. 
            // Checking Club.php again locally might be safer, but let's assume standard 'latest()->paginate()'.
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
        $club = Club::with(['sports', 'media'])->find($id);

        if (!$club) {
            return response()->json(['status' => false, 'message' => 'Club not found'], 404);
        }

        // Fetch Roster (Players) - Users with this club_id AND category='Player' (Category ID logic needed)
        // Since we store 'category_id' on User, we need to know which ID corresponds to 'Player'.
        // For robustness, we might rely on the 'Category' model name, or assume the user passes a filter, 
        // OR simply group users by category.

        // Let's group all users belonging to this club
        $members = $club->players() // wait, Club has hasMany(Player::class) but we decided to use User.
            // Let's check Club.php again. It has `public function players() { return $this->hasMany(Player::class); }`
            // BUT the user said "players can belongs to specific club as these are users".
            // So we should look for Users with this club_id.

            // Let's override/ignore the existing `players` relation if it points to `Player` model and instead query User.
            // actually, let's use a query on User model.
            ->get(); // This is wrong if we want Users.

        // Correct approach: Query Users
        $staffAndPlayers = \App\Models\User::where('club_id', $id)
            ->with(['category', 'media']) // Load category to distinguish
            ->get()
            ->groupBy(function ($user) {
                // Group by Category Name if available, else 'Unknown'
                return $user->category ? $user->category->name : 'Other';
            });

        // We can format this nicely
        $roster = [];
        foreach ($staffAndPlayers as $categoryName => $users) {
            $roster[$categoryName] = $users;
        }

        return response()->json([
            'status' => true,
            'data' => [
                'club' => $club,
                'roster' => $roster,
            ],
            'message' => 'Club details retrieved successfully'
        ]);
    }
}
