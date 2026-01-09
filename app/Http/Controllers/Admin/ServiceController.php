<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('provider')->latest()->paginate(10);
        return view('admin.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        return view('admin.services.show', compact('service'));
    }

    public function destroy(Service $service)
    {
        $service->delete();
        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.services.index');
    }
}
