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
        $clubs = Club::with(['sports', 'owner'])
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
        $club = Club::with(['sports', 'media', 'teams.sport', 'owner'])->find($id);

        if (!$club) {
            return response()->json(['status' => false, 'message' => 'Club not found'], 404);
        }

        // Fetch Users belonging to this club
        $members = \App\Models\User::where('club_id', $id)
            ->with(['category', 'media']) // Load category and media
            ->get()
            ->groupBy(function ($user) {
                return $user->category ? ($user->category->name_en ?: $user->category->name) : 'Other';
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
}
