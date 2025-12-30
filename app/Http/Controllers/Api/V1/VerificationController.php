<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    /**
     * Upload verification documents.
     */
    public function upload(Request $request)
    {
        $user = $request->user();

        if ($user->verification_status === 'approved') {
            return response()->json(['status' => false, 'message' => 'Already approved'], 400);
        }

        $validator = Validator::make($request->all(), [
            'documents' => 'required|array|min:1',
            'documents.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $paths = $user->verification_documents ?? [];

        foreach ($request->file('documents') as $file) {
            $path = $file->store('identifications', 'public'); // Store in 'identifications' folder
            $paths[] = $path;
        }

        $user->verification_documents = $paths;
        $user->verification_status = 'pending'; // Reset to pending if re-uploading
        $user->is_approved = false; // Require admin re-approval
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Documents uploaded. Please wait for admin approval.',
            'data' => [
                'verification_status' => $user->verification_status,
                'documents_count' => count($paths)
            ]
        ]);
    }

    /**
     * Check verification status.
     */
    public function status(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'status' => true,
            'data' => [
                'verification_status' => $user->verification_status,
                'is_approved' => $user->is_approved,
                'rejection_reason' => $user->rejection_reason,
            ]
        ]);
    }
}
