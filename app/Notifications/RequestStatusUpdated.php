<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestStatusUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $serviceRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', \App\Channels\OneSignalChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Request Updated',
            'body' => 'Your request status is now: ' . $this->serviceRequest->status,
            'request_id' => $this->serviceRequest->id,
            'service_id' => $this->serviceRequest->service_id,
            'type' => 'status_update',
            'status' => $this->serviceRequest->status,
            'sender_id' => null
        ];
    }

    public function toOneSignal($notifiable): array
    {
        $status = $this->serviceRequest->status;
        $serviceTitle = $this->serviceRequest->service->title;

        $titles = ['en' => 'Request Updated', 'ar' => 'تحديث الطلب'];
        $messages = ['en' => "Your request status is now: $status", 'ar' => "حالة طلبك الآن: $status"];

        switch ($status) {
            case 'accepted':
                $titles = ['en' => 'Request Accepted', 'ar' => 'تم قبول الطلب'];
                $messages = ['en' => "Your request for $serviceTitle has been accepted!", 'ar' => "تم قبول طلبك لخدمة $serviceTitle!"];
                break;
            case 'rejected':
                $titles = ['en' => 'Request Declined', 'ar' => 'تم رفض الطلب'];
                $messages = ['en' => "Your request for $serviceTitle has been declined.", 'ar' => "تم رفض طلبك لخدمة $serviceTitle."];
                break;
            case 'completed':
                $titles = ['en' => 'Service Completed', 'ar' => 'تم إكمال الخدمة'];
                $messages = ['en' => "Your request for $serviceTitle is marked as completed.", 'ar' => "تم تحديد طلبك لخدمة $serviceTitle كمكتمل."];
                break;
            case 'canceled':
                $titles = ['en' => 'Request Canceled', 'ar' => 'تم إلغاء الطلب'];
                $messages = ['en' => "Your request for $serviceTitle has been canceled.", 'ar' => "تم إلغاء طلبك لخدمة $serviceTitle."];
                break;
        }

        return [
            'title' => $titles,
            'message' => $messages,
            'data' => [
                'type' => 'status_update',
                'request_id' => $this->serviceRequest->id,
            ],
        ];
    }
}
