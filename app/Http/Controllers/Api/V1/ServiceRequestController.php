<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Validator;

use App\Services\OneSignalService;
use App\Traits\FormatsProfileData;
use App\Notifications\ServiceRequestNotification;

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
            'message'      => 'nullable|string|max:500',
            'is_free'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // Prevent booking own service
        if ($service->provider_id == $request->user()->id) {
            return response()->json(['status' => false, 'message' => 'You cannot book your own service'], 400);
        }

        // -------------------------------------------------------
        // Pricing Logic for special service types
        // -------------------------------------------------------
        $specialTypes   = ['performance_experience', 'loan_request'];
        $isSpecialType  = in_array($service->type, $specialTypes);
        $isFreeFlag     = $request->boolean('is_free', false); // Club-invited free

        $price         = $service->price;
        $paymentStatus = 'pending';

        if ($isFreeFlag) {
            // Club invites the player for free — mark as fully paid with 0 price
            $price         = 0;
            $paymentStatus = 'paid';

        } elseif ($isSpecialType) {
            // Count how many COMPLETED or ACTIVE requests this requester already has
            // for this same service TYPE with the same provider
            $usedCount = ServiceRequest::where('requester_id', $request->user()->id)
                ->where('provider_id', $service->provider_id)
                ->whereHas('service', function ($q) use ($service) {
                    $q->where('type', $service->type);
                })
                ->whereNotIn('status', ['canceled', 'rejected'])
                ->count();

            if ($usedCount < 2) {
                // First or second time → free (but recorded as paid)
                $price         = 0;
                $paymentStatus = 'paid';
            } else {
                // Third time onwards → charge from settings
                $settingKey    = $service->type === 'performance_experience'
                    ? 'performance_experience_price'
                    : 'loan_request_price';
                $price         = (float) setting($settingKey, $service->price);
                $paymentStatus = 'pending';
            }
        }

        // Create Request
        $serviceRequest = ServiceRequest::create([
            'service_id'    => $service->id,
            'requester_id'  => $request->user()->id,
            'provider_id'   => $service->provider_id,
            'status'        => 'pending',
            'scheduled_at'  => $request->scheduled_at,
            'message'       => $request->message,
            'price'         => $price,
            'payment_status' => $paymentStatus,
            'is_free'       => $isFreeFlag || ($isSpecialType && $paymentStatus === 'paid'),
        ]);

        \Illuminate\Support\Facades\Log::info("Service Request Created: ID {$serviceRequest->id} by User {$request->user()->id} | type={$service->type} | price={$price} | payment_status={$paymentStatus}");

        $provider = $service->provider;

        // Notify Provider using unified notification system
        try {
            $provider->notify(new ServiceRequestNotification([
                'title'      => ['en' => 'New Service Request', 'ar' => 'طلب خدمة جديد'],
                'body'       => [
                    'en' => "{$request->user()->name} has requested your service: {$service->title}",
                    'ar' => "قام {$request->user()->name} بطلب خدمتك: {$service->title}"
                ],
                'type'       => 'new_request',
                'request_id' => $serviceRequest->id,
                'service_id' => $service->id,
                'sender_id'  => $request->user()->id,
                'push_title' => ['en' => 'New Service Request', 'ar' => 'طلب خدمة جديد'],
                'push_body'  => [
                    'en' => "{$request->user()->name} has requested your service: {$service->title}",
                    'ar' => "قام {$request->user()->name} بطلب خدمتك: {$service->title}"
                ]
            ]));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to notify provider: " . $e->getMessage());
        }

        // Load relations for response
        $serviceRequest->load(['service.media', 'provider.subscription', 'provider.category', 'provider.club', 'requester.subscription', 'requester.category', 'requester.club']);

        $currentUser = $request->user();
        if ($serviceRequest->provider) {
            $serviceRequest->provider_profile = $this->getProfileData($serviceRequest->provider, false, $currentUser);
            $serviceRequest->provider->image  = $serviceRequest->provider->profile_photo_url;
        }
        if ($serviceRequest->requester) {
            $serviceRequest->requester_profile = $this->getProfileData($serviceRequest->requester, false, $currentUser);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Service requested successfully',
            'data'    => $serviceRequest
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

        $provider = $serviceRequest->provider;

        // Notify Provider using unified notification system
        try {
            $provider->notify(new ServiceRequestNotification([
                'title' => ['en' => 'Request Canceled', 'ar' => 'تم إلغاء الطلب'],
                'body' => [
                    'en' => "{$request->user()->name} canceled their request.",
                    'ar' => "قام {$request->user()->name} بإلغاء طلبه."
                ],
                'type' => 'status_update',
                'request_id' => $serviceRequest->id,
                'service_id' => $serviceRequest->service_id,
                'sender_id' => $request->user()->id,
                // Pass push notification data for OneSignalChannel
                'push_title' => ['en' => 'Request Canceled', 'ar' => 'تم إلغاء الطلب'],
                'push_body' => [
                    'en' => "{$request->user()->name} canceled their request.",
                    'ar' => "قام {$request->user()->name} بإلغاء طلبه."
                ]
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

    /**
     * Global Services Activity Log.
     * GET /services-activity
     */
    public function activity(Request $request)
    {
        $requests = ServiceRequest::with([
                'service.media', 
                'provider.subscription', 'provider.category', 'provider.club', 
                'requester.subscription', 'requester.category', 'requester.club'
            ])
            ->whereHas('requester', function ($query) {
                // Filter out requests where the requester has explicitly opted out (false/0)
                $query->where('show_services_activity', true)
                      ->orWhereNull('show_services_activity');
            })
            ->whereHas('provider', function ($query) {
                // Filter out requests where the provider has explicitly opted out
                $query->where('show_services_activity', true)
                      ->orWhereNull('show_services_activity');
            })
            ->latest()
            ->paginate(15);

        $currentUser = $request->user('sanctum');
        
        $requests->getCollection()->transform(function ($req) use ($currentUser) {
            // Format service image
            if ($req->service && $req->service->media->isNotEmpty()) {
                $firstMedia = $req->service->media->first();
                $image = $firstMedia->file_path ?? $firstMedia->image;
                if ($image) {
                    $req->service->featured_image = preg_match('#^https?://#i', $image) ? $image : asset('storage/' . $image);
                }
            } else {
                if ($req->service) {
                    $req->service->featured_image = null;
                }
            }

            // Format provider and requester details
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
            'message' => 'Activity log retrieved successfully'
        ]);
    }
}
