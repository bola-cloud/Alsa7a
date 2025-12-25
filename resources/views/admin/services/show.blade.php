@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2 p-1 rounded" style="background-color: #f1f2f6;">
        <div class="content-header-left col-md-6 col-12">
            <h3 class="content-header-title">Service Details: {{ $service->title }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('admin.menu.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.services.index') }}">{{ __('admin.services.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ $service->title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Info</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th>Title</th>
                                <td>{{ $service->title }}</td>
                            </tr>
                            <tr>
                                <th>Provider</th>
                                <td>{{ $service->provider->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Category/Sport</th>
                                <td>{{ $service->sport->name ?? 'General' }}</td>
                            </tr>
                            <tr>
                                <th>Price</th>
                                <td>{{ $service->price }} {{ $service->currency }}</td>
                            </tr>
                            <tr>
                                <th>Duration</th>
                                <td>{{ $service->duration_minutes }} mins</td>
                            </tr>
                            <tr>
                                <th>Active</th>
                                <td>{{ $service->is_active ? 'Yes' : 'No' }}</td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{{ $service->location }}</td>
                            </tr>
                            <tr>
                                <th>Available Days</th>
                                <td>{{ isset($service->days_available) ? implode(', ', $service->days_available) : '-' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Description</h5>
                        <div class="p-1 border rounded bg-light">
                            {{ $service->description }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection