<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Event\StoreEventRequest;
use App\Http\Requests\Admin\Event\UpdateEventRequest;
use App\Models\Event;
use App\Models\Sport;
use App\Models\Club;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class EventController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $query = Event::with(['sport', 'club'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        $events = $query->paginate(10)->withQueryString();
        $sports = Sport::all();

        return view('admin.events.index', compact('events', 'sports'));
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
        $data['title_en'] = $data['title']['en'];
        $data['title_ar'] = $data['title']['ar'];
        // Removed non-existent 'title' column assignment

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->imageService->upload($request->file('featured_image'), 'events');
        }

        $data['is_featured'] = $request->has('is_featured');

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
        $data['title_en'] = $data['title']['en'];
        $data['title_ar'] = $data['title']['ar'];

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $this->imageService->replace(
                $request->file('featured_image'),
                'events',
                $event->featured_image
            );
        }

        $data['is_featured'] = $request->has('is_featured');

        $event->update($data);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.events.index');
    }

    public function destroy(Event $event)
    {
        $this->imageService->delete($event->featured_image);

        $event->delete();
        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.events.index');
    }

    public function approve(Event $event)
    {
        $event->update(['status' => 'approved']);
        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->back();
    }
}
