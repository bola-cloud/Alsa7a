<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Club;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Get Available Clubs for Claiming
     */
    public function clubsAvailable()
    {
        $clubs = Club::whereNull('user_id')
            ->select('id', 'name', 'logo_url')
            ->get();
        return response()->json(['data' => $clubs]);
    }

    /**
     * Helper to generate and send OTP
     */
    private function generateAndSendOtp(User $user, \App\Services\OtpService $otpService)
    {
        // Generate OTP
        $otp = (string) rand(100000, 999999);

        // Update or Create OTP record
        \App\Models\OtpCode::updateOrCreate(
            ['user_id' => $user->id],
            ['otp' => $otp, 'phone' => $user->phone]
        );

        // Send OTP
        $otpService->sendOtp($user->phone, $otp);
    }

    /**
     * Send OTP API (Re-send or New Request)
     */
    public function sendOtp(Request $request, \App\Services\OtpService $otpService)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        // Use helper to generate and send
        $this->generateAndSendOtp($user, $otpService);

        return response()->json([
            'message' => 'OTP sent to your phone number.',
        ]);
    }

    public function register(Request $request, \App\Services\OtpService $otpService)
    {
        $data = $request->only(['name', 'email', 'phone', 'password', 'onesignal_subscription']);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:6',
            'onesignal_subscription' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check Global Manual Approval Setting
        $autoApprove = setting('manual_user_approval') ? false : true;

        // Normalize OneSignal
        $subscription = $data['onesignal_subscription'] ?? null;
        if ($subscription && is_string($subscription)) {
            $subscription = ['id' => $subscription];
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'is_approved' => $autoApprove,
            'onesignal_subscription' => $subscription,
        ]);

        // Use helper to generate and send
        $this->generateAndSendOtp($user, $otpService);

        return response()->json([
            'message' => 'Registration successful. OTP sent to phone.',
            'user' => $user,
            'requires_verification' => true,
            'requires_approval' => !$autoApprove
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

        if (empty($user->phone_verified_at)) {
            return response()->json([
                'message' => 'Phone number not verified.',
                'requires_verification' => true,
                'user' => $user // sending user so client can get phone/id to request new OTP if needed
            ], 403);
        }

        if ($user->is_blocked) {
            return response()->json([
                'message' => 'Your account has been blocked by the administrator.',
            ], 403);
        }

        if (setting('manual_user_approval') && !$user->is_approved) {
            return response()->json([
                'message' => 'Your account is currently pending approval.',
                'verification_status' => $user->verification_status
            ], 403);
        }

        // Update Subscription if present
        if ($request->has('onesignal_subscription')) {
            $subscription = $request->onesignal_subscription;
            if (is_string($subscription)) {
                $subscription = ['id' => $subscription];
            }
            $user->onesignal_subscription = $subscription;
            $user->save();
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
            // Optional: clear device token on logout?
            // $user->onesignal_subscription = null;
            // $user->save();
        }
        return response()->json(['message' => 'Logged out']);
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        // Developer OTP Check
        if ($request->otp !== '123456') {
            // Check OTP table
            $otpRecord = \App\Models\OtpCode::where('user_id', $user->id)
                ->where('otp', $request->otp)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'message' => 'Invalid OTP code.',
                ], 400);
            }

            // OTP Valid - Delete Record
            $otpRecord->delete();
        }

        $user->forceFill([
            'phone_verified_at' => now(),
            // 'phone_verification_code' => null, // No longer used
        ])->save();

        // Login the user (create token)
        $token = $user->createToken('api-token')->plainTextToken;

        // Handle post-verification flow (questions, etc.)
        $answered = $user->answered_question_ids;
        $complete = $user->questions_complete;

        return response()->json([
            'message' => 'Phone verified successfully.',
            'user' => $user,
            'token' => $token,
            'answered_question_ids' => $answered,
            'questions_complete' => (bool) $complete,
            'redirect_to' => $complete ? null : 'questions',
        ]);
    }

    /**
     * Update OneSignal Subscription (Protected)
     */
    public function updateSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'onesignal_subscription' => 'required', // Removed strict array check
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $subscription = $request->onesignal_subscription;
        if (is_string($subscription)) {
            $subscription = ['id' => $subscription];
        }

        $user = $request->user();
        $user->onesignal_subscription = $subscription;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Subscription updated successfully',
            'data' => $user->onesignal_subscription
        ]);
    }

    public function forgotPassword(Request $request, \App\Services\OtpService $otpService)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        // Use helper to generate and send
        $this->generateAndSendOtp($user, $otpService);

        return response()->json([
            'message' => 'OTP sent to your phone number.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|exists:users,phone',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        // Developer OTP Check
        if ($request->otp !== '123456') {
            // Check OTP table
            $otpRecord = \App\Models\OtpCode::where('user_id', $user->id)
                ->where('otp', $request->otp)
                ->first();

            if (!$otpRecord) {
                return response()->json([
                    'message' => 'Invalid OTP code.',
                ], 400);
            }

            // OTP Valid - Delete Record
            $otpRecord->delete();
        }

        // Update Password
        $user->forceFill([
            'password' => Hash::make($request->password),
            // 'phone_verification_code' => null, // No longer used
            'phone_verified_at' => $user->phone_verified_at ?? now(),
        ])->save();

        return response()->json([
            'message' => 'Password reset successfully. You can now login.',
        ]);
    }
}
