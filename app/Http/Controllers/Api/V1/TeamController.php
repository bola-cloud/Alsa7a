<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Team;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * List teams for a specific club.
     */
    public function index($club_id)
    {
        $club = Club::find($club_id);
        if (!$club) {
            return response()->json(['status' => false, 'message' => 'Club not found'], 404);
        }

        $teams = $club->teams()->with('sport')->latest()->get();

        return response()->json([
            'status' => true,
            'data' => $teams,
            'message' => 'Teams retrieved successfully'
        ]);
    }

    /**
     * Create a new team in a club.
     */
    public function store(Request $request, $club_id)
    {
        $club = Club::find($club_id);
        if (!$club) {
            return response()->json(['status' => false, 'message' => 'Club not found'], 404);
        }

        // Authorization: Only club owner can add teams
        if ($club->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized. Only the club owner can manage teams.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sport_id' => 'required|exists:sports,id',
            'age_group' => 'nullable|string|max:100',
            'short_name' => 'nullable|string|max:50',
            'jersey_color' => 'nullable|string|max:100',
            'coach' => 'nullable|string|max:255',
            'founded_year' => 'nullable|integer',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'sport_id', 'age_group', 'short_name', 'jersey_color', 'coach', 'founded_year']);
        $data['club_id'] = $club->id;
        $data['active'] = $request->boolean('active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->upload($request->file('image'), 'teams');
        }

        $team = Team::create($data);

        return response()->json([
            'status' => true,
            'data' => $team,
            'message' => 'Team created successfully'
        ], 201);
    }

    /**
     * Show a specific team.
     */
    public function show($id)
    {
        $team = Team::with(['club', 'sport'])->find($id);

        if (!$team) {
            return response()->json(['status' => false, 'message' => 'Team not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $team,
            'message' => 'Team details retrieved successfully'
        ]);
    }

    /**
     * Update a team.
     */
    public function update(Request $request, $id)
    {
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['status' => false, 'message' => 'Team not found'], 404);
        }

        $club = $team->club;
        // Authorization: Only club owner can update teams
        if (!$club || $club->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sport_id' => 'required|exists:sports,id',
            'age_group' => 'nullable|string|max:100',
            'short_name' => 'nullable|string|max:50',
            'jersey_color' => 'nullable|string|max:100',
            'coach' => 'nullable|string|max:255',
            'founded_year' => 'nullable|integer',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $request->only(['name', 'sport_id', 'age_group', 'short_name', 'jersey_color', 'coach', 'founded_year']);
        if ($request->has('active')) {
            $data['active'] = $request->boolean('active');
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->replace($request->file('image'), 'teams', $team->image);
        }

        $team->update($data);

        return response()->json([
            'status' => true,
            'data' => $team,
            'message' => 'Team updated successfully'
        ]);
    }

    /**
     * Delete a team.
     */
    public function destroy(Request $request, $id)
    {
        $team = Team::find($id);
        if (!$team) {
            return response()->json(['status' => false, 'message' => 'Team not found'], 404);
        }

        $club = $team->club;
        // Authorization
        if (!$club || $club->user_id !== $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized.'], 403);
        }

        if ($team->image) {
            $this->imageService->delete($team->image);
        }

        $team->delete();

        return response()->json([
            'status' => true,
            'message' => 'Team deleted successfully'
        ]);
    }
}
