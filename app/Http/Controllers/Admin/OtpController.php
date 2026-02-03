<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function index()
    {
        $otpCodes = OtpCode::with('user')->latest()->paginate(20);
        return view('admin.otps.index', compact('otpCodes'));
    }
}
