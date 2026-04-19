<?php

namespace App\Observers;

use App\Models\Club;
use App\Models\Service;
use Illuminate\Support\Str;

class ClubObserver
{
    /**
     * When a new club is created, automatically attach the two special services
     * (performance_experience & loan_request) owned by the club's owner.
     */
    public function created(Club $club): void
    {
        // Only proceed if the club has an owner
        if (!$club->user_id) {
            return;
        }

        // Get default prices from settings (fallback to 1 OMR)
        $performancePrice = (float) (setting('performance_experience_price', 1));
        $loanPrice        = (float) (setting('loan_request_price', 1));

        $servicesToCreate = [
            [
                'type'        => 'performance_experience',
                'title'       => 'تجربة الأداء',
                'description' => 'خدمة تجربة الأداء المقدمة من النادي.',
                'price'       => $performancePrice,
            ],
            [
                'type'        => 'loan_request',
                'title'       => 'طلب إعارة',
                'description' => 'خدمة طلب إعارة لاعب مقدمة من النادي.',
                'price'       => $loanPrice,
            ],
        ];

        foreach ($servicesToCreate as $data) {
            // Avoid duplicate services of the same type for this club owner
            $exists = Service::where('provider_id', $club->user_id)
                ->where('club_id', $club->id)
                ->where('type', $data['type'])
                ->exists();

            if ($exists) {
                continue;
            }

            Service::create([
                'provider_id'    => $club->user_id,
                'club_id'        => $club->id,
                'sport_id'       => $club->sports()->first()?->id, // First sport or null
                'title'          => $data['title'],
                'slug'           => Str::slug($data['title'] . '-' . $club->id . '-' . uniqid()),
                'description'    => $data['description'],
                'price'          => $data['price'],
                'currency'       => 'OMR',
                'days_available' => ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
                'is_active'      => true,
                'type'           => $data['type'],
            ]);
        }
    }

    /**
     * When a club is deleted, also delete its associated services.
     */
    public function deleting(Club $club): void
    {
        $club->services()->delete();
    }
}
