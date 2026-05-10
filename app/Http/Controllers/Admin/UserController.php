<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('category', 'subscription')->latest();

        // General Search (Name, Email, Phone)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) {
                $search = request('search');
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

        // Filter by Subscription Status
        if ($request->filled('subscription_status')) {
            if ($request->subscription_status == 'subscribed') {
                $query->whereHas('subscription', function ($q) {
                    $q->where('status', 'active')
                      ->where('end_date', '>', now());
                });
            } else {
                $query->where(function ($q) {
                    $q->whereDoesntHave('subscription')
                      ->orWhereHas('subscription', function ($q2) {
                          $q2->where('status', '!=', 'active')
                             ->orWhere('end_date', '<=', now());
                      });
                });
            }
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
        $data['is_approved'] = $request->input('is_approved', false);
        $data['is_blocked'] = $request->input('is_blocked', false);

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
        $data['is_blocked'] = $request->has('is_blocked');

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
     * Toggle Block Status
     */
    public function toggleBlock(User $user)
    {
        if ($user->email === 'admin@alsa7a.com') {
            return redirect()->back()->with('swal_error', 'Super Admin cannot be blocked.');
        }
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        $statusKey = $user->is_blocked ? 'blocked_successfully' : 'unblocked_successfully';
        return redirect()->back()->with('swal_success', __("admin.messages.{$statusKey}"));
    }

    /**
     * Approve User Account (Login Access)
     */
    public function approve(User $user)
    {
        $user->is_approved = true;
        $user->save();
        return redirect()->back()->with('swal_success', __('admin.messages.approve_successfully'));
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

    /**
     * Manual Verification Page
     */
    public function manualVerificationIndex()
    {
        return view('admin.users.manual_verification');
    }

    /**
     * Search Users for Select2
     */
    public function searchUsers(Request $request)
    {
        $term = $request->term;
        $users = User::where('name', 'like', '%' . $term . '%')
            ->orWhere('email', 'like', '%' . $term . '%')
            ->orWhere('phone', 'like', '%' . $term . '%')
            ->limit(10)
            ->get();

        $formatted = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->name . ' (' . $user->phone . ')',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'profile_photo_url' => $user->profile_photo_url,
                    'phone_verified_at' => $user->phone_verified_at,
                ],
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Get Teams for a Club (JSON for Select2/Ajax)
     */
    public function getClubTeams(\App\Models\Club $club)
    {
        $teams = $club->teams()->select('id', 'name')->get();
        return response()->json($teams);
    }

    /**
     * Activate Manual Subscription
     */
    public function activateSubscription(User $user)
    {
        $user->subscription()->updateOrCreate(
            [],
            [
                'type' => 'manual',
                'price' => 0,
                'start_date' => now(),
                'end_date' => now()->addYears(10), // Long term subscription
                'status' => 'active',
            ]
        );

        return redirect()->back()->with('swal_success', __('admin.messages.subscription_activated_successfully'));
    }

    /**
     * Cancel Subscription
     */
    public function cancelSubscription(User $user)
    {
        if ($user->subscription) {
            $user->subscription->update([
                'status' => 'cancelled',
                'end_date' => now(), // End it now
            ]);
        }

        return redirect()->back()->with('swal_success', __('admin.messages.subscription_cancelled_successfully'));
    }

    /**
     * Bulk Actions
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'action' => 'required|in:delete,approve,block,unblock,activate_subscription,cancel_subscription',
        ]);

        $ids = collect($request->ids)->reject(function ($id) {
            $user = User::find($id);
            return $user && $user->email === 'admin@alsa7a.com';
        });

        $action = $request->action;

        if ($action === 'delete') {
            User::whereIn('id', $ids)->delete();
            $message = __('admin.messages.deleted_successfully');
        } elseif ($action === 'approve') {
            User::whereIn('id', $ids)->update(['is_approved' => true]);
            $message = __('admin.messages.approve_successfully');
        } elseif ($action === 'block') {
            User::whereIn('id', $ids)->update(['is_blocked' => true]);
            $message = __('admin.messages.blocked_successfully');
        } elseif ($action === 'unblock') {
            User::whereIn('id', $ids)->update(['is_blocked' => false]);
            $message = __('admin.messages.unblocked_successfully');
        } elseif ($action === 'activate_subscription') {
            foreach ($ids as $id) {
                $user = User::find($id);
                if ($user) {
                    $this->activateSubscription($user);
                }
            }
            $message = __('admin.messages.subscription_activated_successfully');
        } elseif ($action === 'cancel_subscription') {
            foreach ($ids as $id) {
                $user = User::find($id);
                if ($user) {
                    $this->cancelSubscription($user);
                }
            }
            $message = __('admin.messages.subscription_cancelled_successfully');
        }

        return redirect()->back()->with('swal_success', $message);
    }
}
