<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Validator;

use App\Services\OneSignalService;
use App\Traits\FormatsProfileData;

class ServiceRequestController extends Controller
{
    use FormatsProfileData;
    protected $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    /**
     * Book a service (Create a Request).
     * POST /services/{id}/request
     */
    public function store(Request $request, $id)
    {
        $service = Service::with(['provider.subscription', 'provider.category', 'provider.club'])->find($id);

        if (!$service) {
            return response()->json(['status' => false, 'message' => 'Service not found'], 404);
        }

        // Validate
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Prevent booking own service
        if ($service->provider_id == $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'You cannot book your own service'], 400);
        }

        // Create Request
        $serviceRequest = ServiceRequest::create([
            'service_id' => $service->id,
            'requester_id' => $request->user()->id,
            'provider_id' => $service->provider_id,
            'status' => 'pending',
            'scheduled_at' => $request->scheduled_at,
            'message' => $request->message,
            'price' => $service->price, // Snapshot price
            'payment_status' => 'pending',
        ]);

        \Illuminate\Support\Facades\Log::info("Service Request Created: ID {$serviceRequest->id} by User {$request->user()->id}");

        // Notify Provider
        $provider = $service->provider; // Assuming relation exists on Service model
        if ($provider && !empty($provider->onesignal_subscription['id'])) {
            $playerId = $provider->onesignal_subscription['id'];

            $titles = ['en' => 'New Service Request', 'ar' => 'طلب خدمة جديد'];
            $messages = [
                'en' => "{$request->user()->name} has requested your service: {$service->title}",
                'ar' => "قام {$request->user()->name} بطلب خدمتك: {$service->title}"
            ];

            $this->oneSignal->sendToPlayers(
                [$playerId],
                $titles,
                $messages,
                ['request_id' => $serviceRequest->id, 'type' => 'new_request']
            );
            $this->oneSignal->sendToPlayers(
                [$playerId],
                $titles,
                $messages,
                ['request_id' => $serviceRequest->id, 'type' => 'new_request']
            );
        }

        // Feature: Notification History (Database)
        try {
            $provider->notify(new \App\Notifications\ServiceRequestNotification([
                'title' => 'New Service Request',
                'body' => "{$request->user()->name} has requested your service: {$service->title}",
                'type' => 'new_request',
                'request_id' => $serviceRequest->id,
                'service_id' => $service->id,
                'sender_id' => $request->user()->id
            ]));
        } catch (\Exception $e) {
        }

        // Feature: Notification History (Database)
        try {
            $provider->notify(new \App\Notifications\ServiceRequestNotification([
                'title' => 'New Service Request',
                'body' => "{$request->user()->name} has requested your service: {$service->title}",
                'type' => 'new_request',
                'request_id' => $serviceRequest->id,
                'service_id' => $service->id,
                'sender_id' => $request->user()->id
            ]));
        } catch (\Exception $e) {
        }

        // Load relations for response
        $serviceRequest->load(['service.media', 'provider.subscription', 'provider.category', 'provider.club', 'requester.subscription', 'requester.category', 'requester.club']);

        $currentUser = $request->user();
        if ($serviceRequest->provider) {
            $serviceRequest->provider_profile = $this->getProfileData($serviceRequest->provider, false, $currentUser);
            // Legacy support
            $serviceRequest->provider->image = $serviceRequest->provider->profile_photo_url;
        }
        if ($serviceRequest->requester) {
            $serviceRequest->requester_profile = $this->getProfileData($serviceRequest->requester, false, $currentUser);
        }

        return response()->json([
            'status' => true,
            'message' => 'Service requested successfully',
            'data' => $serviceRequest
        ], 201);
    }

    /**
     * List my requests.
     * GET /my-requests
     */
    public function index(Request $request)
    {
        $requests = ServiceRequest::where('requester_id', $request->user()->id)
            ->with(['service.media', 'provider.subscription', 'provider.category', 'provider.club', 'requester.subscription', 'requester.category', 'requester.club'])
            ->latest()
            ->paginate(10);

        $currentUser = $request->user();
        // Transform to include full image URL
        $requests->getCollection()->transform(function ($req) use ($currentUser) {
            if ($req->service && $req->service->media->isNotEmpty()) {
                $firstMedia = $req->service->media->first();
                $image = $firstMedia->file_path ?? $firstMedia->image;
                if ($image) {
                    $req->service->featured_image = preg_match('#^https?://#i', $image) ? $image : asset('storage/' . $image);
                }
            } else {
                $req->service->featured_image = null;
            }

            // Fix Provider Image if loaded
            if ($req->provider) {
                $req->provider->image = $req->provider->profile_photo_url;
                $req->provider_profile = $this->getProfileData($req->provider, false, $currentUser);
            }

            if ($req->requester) {
                $req->requester_profile = $this->getProfileData($req->requester, false, $currentUser);
            }

            return $req;
        });

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Requests retrieved successfully'
        ]);
    }
    /**
     * Cancel a request (User/Requester).
     * POST /requests/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::where('requester_id', $request->user()->id)->find($id);

        if (!$serviceRequest) {
            return response()->json(['status' => false, 'message' => 'Request not found'], 404);
        }

        if (in_array($serviceRequest->status, ['completed', 'canceled', 'rejected'])) {
            return response()->json(['status' => false, 'message' => 'Cannot cancel request with status: ' . $serviceRequest->status], 400);
        }

        $serviceRequest->status = 'canceled';
        $serviceRequest->save();

        // Notify Provider
        $provider = $serviceRequest->provider;
        if ($provider && !empty($provider->onesignal_subscription['id'])) {
            $playerId = $provider->onesignal_subscription['id'];
            $titles = ['en' => 'Request Canceled', 'ar' => 'تم إلغاء الطلب'];
            $messages = [
                'en' => "{$request->user()->name} canceled their request.",
                'ar' => "قام {$request->user()->name} بإلغاء طلبه."
            ];
            $this->oneSignal->sendToPlayers(
                [$playerId],
                $titles,
                $messages,
                ['request_id' => $serviceRequest->id, 'type' => 'status_update']
            );
        }

        // Feature: Notification History (Database)
        try {
            $provider->notify(new \App\Notifications\ServiceRequestNotification([
                'title' => 'Request Canceled',
                'body' => "{$request->user()->name} canceled their request.",
                'type' => 'status_update',
                'request_id' => $serviceRequest->id,
                'service_id' => $serviceRequest->service_id,
                'sender_id' => $request->user()->id
            ]));
        } catch (\Exception $e) {
            // Ignore notification errors to not block the response
        }

        return response()->json([
            'status' => true,
            'message' => 'Request canceled successfully',
            'data' => $serviceRequest
        ]);
    }
}
