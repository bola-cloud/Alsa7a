<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Country;
use App\Models\User;
use App\Notifications\AdminGeneralNotification;
use App\Services\OneSignalService;
use App\Support\NotificationTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function create()
    {
        return view('admin.notifications.create', [
            'countries' => Country::where('is_active', true)->ordered()->get(),
            'categories' => Category::orderBy('id')->get(),
            'targets' => NotificationTarget::options(),
        ]);
    }

    /**
     * The users a broadcast targets, used to write the in-app notification
     * rows. The push itself is aimed with OneSignal tags, not with this list.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function audienceQuery(Request $request)
    {
        $query = User::query();

        // 'all' (or nothing) means every country, users without one included.
        if ($request->filled('country_id') && $request->country_id !== 'all') {
            $query->where('country_id', $request->country_id);
        }

        // No categories ticked means every category.
        $categoryIds = array_filter((array) $request->input('category_ids', []));

        if (! empty($categoryIds)) {
            $query->whereIn('category_id', $categoryIds);
        }

        return $query;
    }

    /**
     * Live audience size for the form, so the admin sees who they are about to
     * reach before sending instead of guessing.
     */
    public function audience(Request $request)
    {
        $query = $this->audienceQuery($request);

        return response()->json([
            'total' => (clone $query)->count(),
            'reachable' => (clone $query)->whereNotNull('onesignal_subscription')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $targetType = $request->input('target_type', NotificationTarget::NONE);

        $request->validate(array_merge([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'country_id' => 'nullable',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:categories,id',
        ], NotificationTarget::rules($targetType)));

        // A dangling id would land the user on a "couldn't open" toast, which
        // is worse than refusing to send.
        if (! NotificationTarget::idExists($targetType, $request->input('target_id'))) {
            return back()->withInput()
                ->withErrors(['target_id' => __('admin.notifications.target_id_missing')]);
        }

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('notifications', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        $metaData = NotificationTarget::payload(
            $targetType,
            $request->input('target_id'),
            $request->input('target_url')
        );

        // sendPush = false: this notification only writes the in-app record.
        // The push goes out below as a single OneSignal request.
        $notification = new AdminGeneralNotification($request->title, $request->message, $imageUrl, $metaData, false);

        $recipients = $this->storeDatabaseRecords($this->audienceQuery($request), $notification);

        if ($recipients === 0) {
            return redirect()->route('admin.notifications.create')
                ->with('error', __('admin.notifications.no_recipients'));
        }

        $payload = $notification->oneSignalPayload();

        $options = array_filter([
            'big_picture' => $payload['big_picture'] ?? null,
            'ios_attachments' => $payload['ios_attachments'] ?? null,
        ]);

        // One OneSignal request either way: the whole-app segment when there is
        // no filter, tag filters otherwise. Never a request per recipient --
        // that is reserved for personal notifications (chat, request status).
        $oneSignal = app(OneSignalService::class);

        $filters = $oneSignal->buildAudienceFilters(
            $request->input('country_id'),
            (array) $request->input('category_ids', [])
        );

        $result = empty($filters)
            ? $oneSignal->sendBroadcast($payload['title'], $payload['message'], $payload['data'] ?? null, $options)
            : $oneSignal->sendToFilters($filters, $payload['title'], $payload['message'], $payload['data'] ?? null, $options);

        if (! ($result['status'] ?? false)) {
            return redirect()->route('admin.notifications.create')
                ->with('error', __('admin.notifications.error'));
        }

        return redirect()->route('admin.notifications.create')
            ->with('success', __('admin.notifications.success') . ' (' . $recipients . ')');
    }

    /**
     * Write the in-app notification rows for the whole audience.
     *
     * Notification::send() would queue one job per user -- thousands of them
     * for a single identical payload -- so the rows go in as bulk inserts.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return int  number of recipients
     */
    protected function storeDatabaseRecords($query, AdminGeneralNotification $notification)
    {
        $data = json_encode($notification->toArray(null), JSON_UNESCAPED_UNICODE);
        $now = now();
        $type = get_class($notification);
        $total = 0;

        $query->select('id')->chunkById(1000, function ($users) use ($data, $now, $type, &$total) {
            $rows = $users->map(function ($user) use ($data, $now, $type) {
                return [
                    'id' => (string) Str::uuid(),
                    'type' => $type,
                    'notifiable_type' => User::class,
                    'notifiable_id' => $user->id,
                    'data' => $data,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            DB::table('notifications')->insert($rows);
            $total += count($rows);
        });

        return $total;
    }

    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->take(10)->get();
        $unreadCount = $user->unreadNotifications->count();

        return response()->json([
            'count' => $unreadCount,
            'notifications' => $notifications->map(function($n) {
                return [
                    'id' => $n->id,
                    'read_at' => $n->read_at,
                    'data' => array_merge($n->data, [
                        'registered_text' => __('admin.notifications.registered')
                    ]),
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            })
        ]);
    }

    public function markAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markSingleAsRead($id)
    {
        $notification = auth()->user()->unreadNotifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }
}
