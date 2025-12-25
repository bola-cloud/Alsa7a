<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::with(['service', 'requester', 'provider'])->latest()->paginate(10);
        return view('admin.service_requests.index', compact('requests'));
    }

    public function show(ServiceRequest $request)
    {
        return view('admin.service_requests.show', compact('request')); // Change variable name to avoid conflict with Request $request
    }
}
