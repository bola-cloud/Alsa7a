@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.services.index') }}">{{ __('admin.services.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($service->title, 20) }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.services.title') }}</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    @if($service->media->first())
                        <!-- Assuming service has media, otherwise showing placeholder -->
                        <img src="{{ asset($service->media->first()->file_path) }}" class="img-fluid rounded mb-3"
                            style="max-height: 200px;">
                    @else
                        <div class="bg-light rounded mb-3 d-flex align-items-center justify-content-center"
                            style="height: 150px;">
                            <i class="la la-image font-large-3 text-muted"></i>
                        </div>
                    @endif

                    <h4 class="card-title font-weight-bold">{{ $service->title }}</h4>
                    <p class="text-muted mb-2">{{ $service->sport->name ?? 'General' }}</p>

                    <div class="mb-3">
                        <span class="badge badge-pill badge-{{ $service->is_active ? 'success' : 'danger' }} px-3 py-1">
                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h3 class="text-primary font-weight-bold mb-3">
                        {{ $service->price }} <small>{{ $service->currency }}</small>
                    </h3>

                    <div class="row text-left mt-4">
                        <div class="col-12 py-2 border-bottom">
                            <strong>Provider:</strong> <span
                                class="float-right">{{ $service->provider->name ?? '-' }}</span>
                        </div>
                        <div class="col-12 py-2 border-bottom">
                            <strong>Duration:</strong> <span class="float-right">{{ $service->duration_minutes }}
                                mins</span>
                        </div>
                        <div class="col-12 py-2 border-bottom">
                            <strong>Available Days:</strong> <br>
                            <span class="d-block mt-1 text-muted small">
                                {{ isset($service->days_available) ? implode(', ', $service->days_available) : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h4 class="card-title">Description & Details</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="font-weight-bold text-muted text-uppercase small">Description</label>
                        <p class="text-dark">{{ $service->description }}</p>
                    </div>

                    <div class="mb-4">
                        <label class="font-weight-bold text-muted text-uppercase small">Location</label>
                        <p class="text-dark"><i class="la la-map-marker text-danger"></i> {{ $service->location }}</p>
                    </div>

                    @if($service->media->count() > 1)
                        <label class="font-weight-bold text-muted text-uppercase small mb-3">Gallery</label>
                        <div class="row">
                            @foreach($service->media as $media)
                                <div class="col-md-3 col-6 mb-3">
                                    <a href="{{ asset($media->file_path) }}" target="_blank">
                                        <img src="{{ asset($media->file_path) }}" class="img-fluid rounded border hover-shadow"
                                            style="height: 100px; object-fit: cover; width: 100%;">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="card-footer bg-white border-top-0 text-right pb-4">
                    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary mr-2">
                        <i class="la la-arrow-left"></i> {{ __('admin.buttons.back') }}
                    </a>
                    <form action="{{ route('admin.services.toggle', $service->id) }}" method="POST"
                        style="display:inline-block">
                        @csrf
                        <button type="submit" class="btn {{ $service->is_active ? 'btn-danger' : 'btn-success' }}">
                            <i class="la la-power-off"></i> {{ $service->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection