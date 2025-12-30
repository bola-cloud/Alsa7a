<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->only(['name', 'email', 'phone', 'password']);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check Global Manual Approval Setting (simulated for now, would be DB or config)
        $autoApprove = setting('manual_user_approval') ? false : true;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'is_approved' => $autoApprove,
        ]);

        if (!$user->is_approved) {
            return response()->json([
                'message' => 'Registration successful. Your account is pending admin approval.',
                'user' => $user,
                'requires_approval' => true
            ], 200);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // include question completion info and redirect hint
        $answered = $user->answered_question_ids;
        $complete = $user->questions_complete;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'answered_question_ids' => $answered,
            'questions_complete' => (bool) $complete,
            'redirect_to' => $complete ? null : 'questions',
        ], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['phone', 'password']);

        $validator = Validator::make($credentials, [
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $credentials['phone'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_approved) {
            return response()->json([
                'message' => 'Your account is currently pending approval.',
                'verification_status' => $user->verification_status
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $answered = $user->answered_question_ids;
        $complete = $user->questions_complete;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'answered_question_ids' => $answered,
            'questions_complete' => (bool) $complete,
            'redirect_to' => $complete ? null : 'questions',
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }
        return response()->json(['message' => 'Logged out']);
    }
}
