<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Event\StoreEventRequest;
use App\Http\Requests\Admin\Event\UpdateEventRequest;
use App\Models\Event;
use App\Models\Sport;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['sport', 'club'])->latest()->paginate(10);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $sports = Sport::all();
        $clubs = Club::all();
        return view('admin.events.create', compact('sports', 'clubs'));
    }

    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['title']['en']);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('events', 'public');
        }

        Event::create($data);

        $this->flashSuccess(__('admin.messages.created'));
        return redirect()->route('admin.events.index');
    }

    public function show(Event $event, Request $request)
    {
        $event->load(['sport', 'club']);

        $bookings = $event->bookings()
            ->with('user')
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('ticket_number', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                        ->orWhereHas('user', function ($u) use ($request) {
                            $u->where('name', 'like', '%' . $request->search . '%')
                                ->orWhere('email', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.events.show', compact('event', 'bookings'));
    }

    public function edit(Event $event)
    {
        $sports = Sport::all();
        $clubs = Club::all();
        return view('admin.events.edit', compact('event', 'sports', 'clubs'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $data = $request->validated();

        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']['en']);
        }

        if ($request->hasFile('featured_image')) {
            if ($event->featured_image && Storage::disk('public')->exists($event->featured_image)) {
                Storage::disk('public')->delete($event->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('events', 'public');
        }

        $event->update($data);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.events.index');
    }

    public function destroy(Event $event)
    {
        if ($event->featured_image && Storage::disk('public')->exists($event->featured_image)) {
            Storage::disk('public')->delete($event->featured_image);
        }
        $event->delete();
        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.events.index');
    }
}
