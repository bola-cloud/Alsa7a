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

        // General Search (Name, Email, Phone)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by Role (Category)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by Pending Approvals / Verifications
        if ($request->has('pending_approval')) {
            $query->where('is_approved', false);
        }
        if ($request->has('pending_verification')) {
            $query->where('verification_status', 'pending');
        }

        $users = $query->paginate(20)->withQueryString();
        // Assuming we need categories for filter, let's fetch them or use view composer if exists.
        // I'll fetch them here to be safe.
        $categories = \App\Models\Category::all();

        return view('admin.users.index', compact('users', 'categories'));
    }

    public function show(User $user)
    {
        $user->load('category', 'answers'); // Eager load

        $questions = collect();
        if ($user->category_id) {
            $questions = \App\Models\Question::where('category_id', $user->category_id)->get();
        }

        return view('admin.users.show', compact('user', 'questions'));
    }

    /**
     * Approve User Account (Login Access)
     */
    public function approve(User $user)
    {
        $user->is_approved = true;
        $user->save();
        $this->flashSuccess('User approved successfully');
        return redirect()->back();
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

        $this->flashSuccess('Verification status updated');
        return redirect()->back();
    }
}
