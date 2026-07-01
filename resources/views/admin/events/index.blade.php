@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.events.index') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.events.index') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12 text-right">
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary shadow-soft">
                <i class="la la-plus"></i> {{ __('admin.buttons.add_new') }}
            </a>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('admin.events.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.categories.search') }}</label>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control pl-4"
                                placeholder="{{ __('admin.events.title') }} / Venue..." value="{{ request('search') }}">
                            <i class="la la-search position-absolute" style="top: 10px; left: 10px; color: #b0afb5;"></i>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.sports.title') }}</label>
                        <select name="sport_id" class="form-control">
                            <option value="">{{ __('admin.categories.all') }}</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                                    {{ $sport->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="la la-filter"></i> {{ __('admin.buttons.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @forelse($events as $event)
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="admin-card">
                    <div class="card-img-wrapper position-relative" style="height: 180px; padding: 0;">
                        @if($event->is_featured)
                            <span class="badge badge-warning position-absolute" style="top: 10px; right: 10px; z-index: 10;">
                                <i class="la la-star text-white"></i> {{ __('admin.events.featured') }}
                            </span>
                        @endif
                        @if($event->featured_image)
                            <img src="{{ $event->featured_image }}" class="card-img-top w-100 h-100" style="object-fit: cover;"
                                alt="{{ $event->title }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-light text-muted">
                                <i class="la la-calendar font-large-3"></i>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="card-title text-truncate" title="{{ $event->title }}">
                            {{ $event->title }}
                        </h5>

                        <div class="mb-2">
                            <p class="card-text small text-muted mb-1">
                                <i class="la la-map-marker text-primary"></i> {{ $event->venue ?? 'N/A' }}
                            </p>
                            <p class="card-text small text-muted mb-1">
                                <i class="la la-clock-o text-warning"></i>
                                {{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('Y-m-d H:i') : 'N/A' }}
                            </p>
                            <p class="font-weight-bold text-success mb-0">
                                {{ $event->price > 0 ? number_format($event->price, 0) . ' OMR' : 'Free' }}
                            </p>
                        </div>

                        <div class="card-actions d-flex flex-column gap-2 mt-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                @php
                                    $statusColor = match($event->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning'
                                    };
                                @endphp
                                <span class="badge badge-{{ $statusColor }}">{{ __('admin.status.'.$event->status) }}</span>

                                @if($event->status === 'pending')
                                    <form action="{{ route('admin.events.approve', $event->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success round py-0 px-2" style="font-size: 11px;">
                                            <i class="la la-check"></i> {{ __('admin.users.approve') }}
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-outline-info round"
                                    title="{{ __('admin.buttons.view') }}">
                                    <i class="la la-eye"></i> {{ __('admin.buttons.view') }}
                                </a>

                                <div class="btn-group">
                                    <a href="{{ route('admin.events.edit', $event->id) }}"
                                        class="btn btn-sm btn-outline-primary round" title="{{ __('admin.buttons.edit') }}">
                                        <i class="la la-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST"
                                        onsubmit="return confirm('{{ __('admin.buttons.confirm_delete') }}');"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger round border-0 ml-1"
                                            title="{{ __('admin.buttons.delete') }}">
                                            <i class="la la-trash font-medium-3"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-muted">{{ __('admin.categories.no_records') }}</h4>
                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary mt-2">
                            {{ __('admin.buttons.add_new') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="row">
        <div class="col-12">
            {{ $events->links() }}
        </div>
    </div>
@endsection