<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with(['provider', 'sport'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('provider', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        $services = $query->paginate(10)->withQueryString();
        $sports = \App\Models\Sport::all();

        return view('admin.services.index', compact('services', 'sports'));
    }

    public function show(Service $service)
    {
        $service->load(['provider', 'sport', 'club', 'media']);
        return view('admin.services.show', compact('service'));
    }

    public function toggle(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);
        $status = $service->is_active ? __('admin.messages.activated') : __('admin.messages.deactivated');
        $this->flashSuccess($status);
        return redirect()->back();
    }

    public function toggleFeatured(Service $service)
    {
        $service->update(['is_featured' => !$service->is_featured]);
        $status = $service->is_featured ? __('admin.messages.featured_activated') : __('admin.messages.featured_deactivated');
        $this->flashSuccess($status);
        return redirect()->back();
    }

    public function destroy(Service $service)
    {
        $service->delete();
        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.services.index');
    }
}
