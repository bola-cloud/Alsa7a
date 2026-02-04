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

        $users = $query->paginate(15)->withQueryString();
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

    public function create()
    {
        $categories = \App\Models\Category::all();
        $clubs = \App\Models\Club::all();
        return view('admin.users.create', compact('categories', 'clubs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'is_approved' => 'boolean',
            'club_id' => 'nullable|exists:clubs,id',
            'team_id' => 'nullable|exists:teams,id',
            'position' => 'nullable|string|max:100',
            'number' => 'nullable|string|max:10',
        ]);

        $data = $request->except(['password', 'password_confirmation']);
        $data['password'] = bcrypt($request->password);
        $data['is_approved'] = $request->input('is_approved', false); // Default false if not checked? Form handle it.

        User::create($data);

        return redirect()->route('admin.users.index')->with('swal_success', __('admin.messages.created_successfully'));
    }
    public function edit(User $user)
    {
        if ($user->email === 'admin@alsa7a.com') {
            return redirect()->route('admin.users.index')->with('swal_error', 'Super Admin cannot be edited.');
        }
        $categories = \App\Models\Category::all();
        $clubs = \App\Models\Club::all();
        $teams = collect();
        if ($user->club_id) {
            $teams = \App\Models\Team::where('club_id', $user->club_id)->get();
        }
        return view('admin.users.edit', compact('user', 'categories', 'clubs', 'teams'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->email === 'admin@alsa7a.com') {
            return redirect()->route('admin.users.index')->with('swal_error', 'Super Admin cannot be updated.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_approved' => 'boolean',
            'club_id' => 'nullable|exists:clubs,id',
            'team_id' => 'nullable|exists:teams,id',
            'position' => 'nullable|string|max:100',
            'number' => 'nullable|string|max:10',
        ]);

        $data = $request->except(['password', 'password_confirmation']);

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        } else {
            unset($data['password']);
        }

        // Handle checkboxes for boolean
        $data['is_admin'] = $request->has('is_admin');
        $data['is_approved'] = $request->has('is_approved');

        $user->update($data);

        return redirect()->route('admin.users.index')->with('swal_success', __('admin.messages.updated_successfully'));
    }

    public function destroy(User $user)
    {
        if ($user->email === 'admin@alsa7a.com') {
            return redirect()->back()->with('swal_error', 'Super Admin cannot be deleted.');
        }
        $user->delete();
        return redirect()->back()->with('swal_success', __('admin.messages.deleted_successfully'));
    }

    /**
     * Approve User Account (Login Access)
     */
    public function approve(User $user)
    {
        $user->is_approved = true;
        $user->save();
        return redirect()->back()->with('swal_success', 'User approved successfully');
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
            $user->is_approved = false;
        } else {
            $user->is_approved = true;
            $user->rejection_reason = null;
        }

        $user->save();

        return redirect()->back()->with('swal_success', 'Verification status updated');
    }

    /**
     * Manually Verify Phone Number
     */
    public function verifyPhone(User $user)
    {
        $user->forceFill([
            'phone_verified_at' => now(),
        ])->save();

        // Delete any pending OTPs
        \App\Models\OtpCode::where('user_id', $user->id)->delete();

        return redirect()->back()->with('swal_success', __('admin.otps.verified_successfully'));
    }
}
