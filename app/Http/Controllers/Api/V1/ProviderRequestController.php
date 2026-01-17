<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Notifications\RequestStatusUpdated;

use App\Services\OneSignalService;

class ProviderRequestController extends Controller
{
    protected $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    /**
     * List incoming requests for the provider.
     */
    public function index(Request $request)
    {
        // Get requests where the authenticated user is the provider
        $requests = ServiceRequest::with(['service.media', 'requester'])
            ->where('provider_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        // Transform to include full image URL
        $requests->getCollection()->transform(function ($req) {
            if ($req->service && $req->service->media->isNotEmpty()) {
                $firstMedia = $req->service->media->first();
                $image = $firstMedia->file_path ?? $firstMedia->image; // Handle different column names
                if ($image) {
                    $req->service->featured_image = preg_match('#^https?://#i', $image) ? $image : asset('storage/' . $image);
                }
            } else {
                $req->service->featured_image = null;
            }

            // Fix Requester Image
            if ($req->requester) {
                $req->requester->image = $req->requester->profile_photo_url;
                if ($req->requester->profile_photo_path) {
                    $url = url('storage/' . $req->requester->profile_photo_path);
                    $req->requester->image = $url;
                    $req->requester->profile_photo_url = $url;
                }
            }

            return $req;
        });

        return response()->json([
            'status' => true,
            'data' => $requests,
            'message' => 'Incoming requests retrieved successfully'
        ]);
    }

    /**
     * Update request status (Accept/Reject).
     */
    public function updateStatus(Request $request, $id)
    {
        $serviceRequest = ServiceRequest::with('requester', 'service')->where('provider_id', $request->user()->id)
            ->find($id);

        if (!$serviceRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Request not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,rejected,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldStatus = $serviceRequest->status;
        $serviceRequest->status = $request->status;
        $serviceRequest->save();

        // Notify Requester (Database Notification)
        if ($serviceRequest->requester) {
            try {
                $serviceRequest->requester->notify(new RequestStatusUpdated($serviceRequest));
            } catch (\Exception $e) {
                // Log error but don't break flow
                \Illuminate\Support\Facades\Log::error("Failed to send DB notification: " . $e->getMessage());
            }

            // PUSH NOTIFICATION
            if (!empty($serviceRequest->requester->onesignal_subscription['id'])) {
                $playerId = $serviceRequest->requester->onesignal_subscription['id'];

                $titles = [];
                $messages = [];

                switch ($request->status) {
                    case 'accepted':
                        $titles = ['en' => 'Request Accepted', 'ar' => 'تم قبول الطلب'];
                        $messages = [
                            'en' => "Your request for {$serviceRequest->service->title} has been accepted!",
                            'ar' => "تم قبول طلبك لخدمة {$serviceRequest->service->title}!"
                        ];
                        break;
                    case 'rejected':
                        $titles = ['en' => 'Request Declined', 'ar' => 'تم رفض الطلب'];
                        $messages = [
                            'en' => "Your request for {$serviceRequest->service->title} has been declined.",
                            'ar' => "تم رفض طلبك لخدمة {$serviceRequest->service->title}."
                        ];
                        break;
                    case 'completed':
                        $titles = ['en' => 'Service Completed', 'ar' => 'تم إكمال الخدمة'];
                        $messages = [
                            'en' => "Your request for {$serviceRequest->service->title} is marked as completed.",
                            'ar' => "تم تحديد طلبك لخدمة {$serviceRequest->service->title} كمكتمل."
                        ];
                        break;
                    case 'canceled':
                        $titles = ['en' => 'Request Canceled', 'ar' => 'تم إلغاء الطلب'];
                        $messages = [
                            'en' => "Your request for {$serviceRequest->service->title} has been canceled.",
                            'ar' => "تم إلغاء طلبك لخدمة {$serviceRequest->service->title}."
                        ];
                        break;
                }

                if (!empty($messages)) {
                    $this->oneSignal->sendToPlayers(
                        [$playerId],
                        $titles,
                        $messages,
                        ['request_id' => $serviceRequest->id, 'type' => 'status_update']
                    );
                }
            }
        }

        // Create Chat Conversation if Accepted
        if ($request->status === 'accepted') {
            $existingStub = \App\Models\Conversation::where('service_request_id', $serviceRequest->id)->exists();
            if (!$existingStub) {
                \App\Models\Conversation::create([
                    'service_request_id' => $serviceRequest->id,
                    'user_one_id' => $request->user()->id, // Provider
                    'user_two_id' => $serviceRequest->requester_id, // Requester
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Request status updated to ' . $request->status,
            'data' => $serviceRequest
        ]);
    }
}
