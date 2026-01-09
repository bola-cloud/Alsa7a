<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $requests = ServiceRequest::with(['service', 'provider', 'requester'])->latest()->paginate(10);
        return view('admin.service_requests.index', compact('requests'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $serviceRequest = ServiceRequest::with(['service', 'provider', 'requester', 'conversation'])->findOrFail($id);
        return view('admin.service_requests.show', compact('serviceRequest'));
    }

    /**
     * Admin Override Status
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,accepted,rejected,completed,canceled',
        ]);

        $serviceRequest = ServiceRequest::findOrFail($id);
        $serviceRequest->status = $request->status;
        $serviceRequest->save();

        $this->flashSuccess('Status updated successfully');
        return redirect()->back();
    }
}
