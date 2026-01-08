<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@alsa7a.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'phone' => '01000000000',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        if (!$admin->is_admin) {
            $admin->is_admin = true;
            $admin->save();
        }
    }
}
