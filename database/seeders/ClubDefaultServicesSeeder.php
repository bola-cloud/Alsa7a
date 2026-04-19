<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClubDefaultServicesSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = Club::with('sports')->whereNotNull('user_id')->get();

        if ($clubs->isEmpty()) {
            $this->command->info('No clubs with owners found. Skipping.');
            return;
        }

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

        foreach ($clubs as $club) {
            foreach ($servicesToCreate as $data) {
                // Skip if service of this type already exists for this club+owner
                $exists = Service::where('provider_id', $club->user_id)
                    ->where('club_id', $club->id)
                    ->where('type', $data['type'])
                    ->exists();

                if ($exists) {
                    $this->command->line("  [skip] Club #{$club->id} already has {$data['type']}");
                    continue;
                }

                Service::create([
                    'provider_id'    => $club->user_id,
                    'club_id'        => $club->id,
                    'sport_id'       => $club->sports->first()?->id,
                    'title'          => $data['title'],
                    'slug'           => Str::slug($data['title'] . '-' . $club->id . '-' . uniqid()),
                    'description'    => $data['description'],
                    'price'          => $data['price'],
                    'currency'       => 'OMR',
                    'days_available' => ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'],
                    'is_active'      => true,
                    'type'           => $data['type'],
                ]);

                $this->command->info("  [created] {$data['type']} for Club #{$club->id} ({$club->name})");
            }
        }

        $this->command->info('Done seeding default services for existing clubs.');
    }
}
