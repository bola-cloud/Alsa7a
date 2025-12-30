<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('category')->latest();

        // Filter by Pending Approvals / Verifications
        if ($request->has('pending_approval')) {
            $query->where('is_approved', false);
        }
        if ($request->has('pending_verification')) {
            $query->where('verification_status', 'pending');
        }

        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Approve User Account (Login Access)
     */
    public function approve(User $user)
    {
        $user->is_approved = true;
        $user->save();
        return redirect()->back()->with('success', 'User approved successfully');
    }

    /**
     * Verify Documents (Category Verification)
     */
    public function verifyDocuments(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected',
        ]);

        $user->verification_status = $request->status;
        if ($request->status === 'rejected') {
            $user->rejection_reason = $request->rejection_reason;
            $user->is_approved = false; // Revoke access if rejected? Or just verification status. user request implied strictness.
        } else {
            $user->is_approved = true; // Auto-approve login if docs verified
            $user->rejection_reason = null;
        }

        $user->save();

        return redirect()->back()->with('success', 'Verification status updated');
    }
}
