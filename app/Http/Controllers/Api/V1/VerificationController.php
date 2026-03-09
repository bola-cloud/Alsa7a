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
        $user = $request->user()->load('category');

        if ($user->verification_status === 'approved') {
            return response()->json(['status' => false, 'message' => 'Already approved'], 400);
        }

        $category = $user->category;
        $fields = $category ? $category->verification_fields : null;

        if ($fields && is_array($fields) && count($fields) > 0) {
            $rules = [];
            foreach ($fields as $field) {
                $fieldId = $field['id'];
                if ($field['type'] === 'file') {
                    $rules[$fieldId] = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
                } else {
                    $rules[$fieldId] = 'required|string|max:1000';
                }
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
            }

            $verificationData = [];
            foreach ($fields as $field) {
                $fieldId = $field['id'];
                if ($field['type'] === 'file' && $request->hasFile($fieldId)) {
                    $path = $request->file($fieldId)->store('identifications', 'public');
                    $verificationData[$fieldId] = $path;
                } else {
                    $verificationData[$fieldId] = $request->input($fieldId);
                }
            }
            $user->verification_documents = $verificationData;
        } else {
            // Fallback to legacy documents[] array
            $validator = Validator::make($request->all(), [
                'documents' => 'required|array|min:1',
                'documents.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
            }

            $paths = [];
            foreach ($request->file('documents') as $file) {
                $path = $file->store('identifications', 'public');
                $paths[] = $path;
            }
            $user->verification_documents = $paths;
        }

        $user->verification_status = 'pending';
        $user->is_approved = false;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Verification details submitted. Please wait for admin approval.',
            'data' => [
                'verification_status' => $user->verification_status,
                'documents_count' => is_array($user->verification_documents) ? count($user->verification_documents) : 0
            ]
        ]);
    }

    /**
     * Check verification status.
     */
    public function status(Request $request)
    {
        $user = $request->user()->load('category');
        $category = $user->category;

        return response()->json([
            'status' => true,
            'data' => [
                'verification_status' => $user->verification_status,
                'is_approved' => $user->is_approved,
                'rejection_reason' => $user->rejection_reason,
                'verification_documents' => (function () use ($user) {
                    $docs = $user->verification_documents;
                    if (!$docs)
                        return null;
                    if (!is_array($docs))
                        return $docs;

                    return collect($docs)->map(function ($value, $key) {
                        if (is_string($value) && (str_contains($value, '.') || str_contains($value, '/'))) {
                            return url('storage/' . $value);
                        }
                        return $value;
                    });
                })(),
                'requires_verification' => $category ? (bool) $category->requires_verification : false,
                'verification_requirements_en' => $category ? $category->verification_requirements_en : null,
                'verification_requirements_ar' => $category ? $category->verification_requirements_ar : null,
                'verification_fields' => $category ? $category->verification_fields : [],
            ]
        ]);
    }
}
