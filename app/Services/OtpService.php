<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OtpService
{
    private $baseUrl = 'https://otp.wasl-x.com/api/v1/otp';
    private $token;

    public function __construct()
    {
        $this->token = env('WASL_OTP_TOKEN');
    }

    public function sendOtp($phone, $otp)
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ])->post($this->baseUrl . '/send', [
                        'phone' => $phone,
                        'otp' => $otp,
                    ]);

            if ($response->successful()) {
                Log::info('OTP sent successfully to ' . $phone);
                return true;
            } else {
                Log::error('Failed to send OTP to ' . $phone . ': ' . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('OTP Service Error: ' . $e->getMessage());
            return false;
        }
    }
}
