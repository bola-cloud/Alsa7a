<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    /**
     * Display a listing of marketplace job requests.
     *
     * Country filtering is automatic (MarketRequest uses HasCountryScope),
     * same strict-per-country behavior as every other admin listing.
     */
    public function index(Request $request)
    {
        $query = MarketRequest::with(['user', 'club', 'category'])
            ->withCount('applications')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(15)->withQueryString();
        $categories = Category::where('is_marketplace', true)->orderBy('name_en')->get();

        return view('admin.market_requests.index', compact('requests', 'categories'));
    }

    /**
     * Display one marketplace job request with its full applicant list.
     */
    public function show($id)
    {
        $marketRequest = MarketRequest::with([
            'user', 'club', 'category', 'questions',
            // Everything the applicants table needs, eager loaded so the page
            // does not fire a query per applicant.
            'applications.user.category',
            'applications.user.club',
            'applications.user.ownedClub',
            'applications.user.answers.question',
            'applications.user.ratingsReceived',
            'applications.answers.question',
        ])->findOrFail($id);

        return view('admin.market_requests.show', compact('marketRequest'));
    }

    /**
     * Admin override: close or reopen a listing.
     *
     * Kept separate from the mobile close() endpoint (which is poster-only
     * and one-way) — this lets an admin moderate a listing either direction,
     * e.g. reopen one a poster closed by mistake, or close one that violates
     * the rules.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,closed',
        ]);

        $marketRequest = MarketRequest::findOrFail($id);
        $marketRequest->update(['status' => $request->status]);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->back();
    }

    /**
     * Delete a marketplace job request (and its applications, cascade).
     */
    public function destroy($id)
    {
        $marketRequest = MarketRequest::findOrFail($id);
        $marketRequest->delete();

        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.market_requests.index');
    }
}
