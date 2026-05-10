<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\NewUserNotification;
use Illuminate\Support\Facades\Notification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Don't notify for admin users created by seeds or admin panel if needed
        // But generally, any new user should notify the super admin
        
        $admins = User::where('email', 'admin@alsa7a.com')->get();
        
        Notification::send($admins, new NewUserNotification($user));
    }
}
