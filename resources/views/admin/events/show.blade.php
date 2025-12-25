@extends('layouts.admin')

@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row mb-2">
                <div class="content-header-left col-md-6 col-12">
                    <h3 class="content-header-title">{{ $event->title }}</h3>
                    <div class="row breadcrumbs-top">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a
                                        href="{{ route('dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('admin.events.index') }}">{{ __('admin.events.index') }}</a></li>
                                <li class="breadcrumb-item active">{{ $event->title }}</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="content-header-right col-md-6 col-12">
                    <div class="btn-group float-md-right">
                        <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-primary round px-2">
                            <i class="ft-edit icon-left"></i> {{ __('admin.buttons.edit') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="">
                <!-- Event Overview Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="row">
                                <!-- Image Column -->
                                <div class="col-lg-4 col-md-5 mb-2 mb-md-0">
                                    <div class="event-image-wrapper rounded overflow-hidden shadow-sm"
                                        style="max-height: 250px;">
                                        @if($event->featured_image)
                                            <img src="{{ $event->featured_image }}"
                                                class="img-fluid w-100 h-100 object-fit-cover" alt="{{ $event->title }}">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center h-100"
                                                style="min-height: 200px;">
                                                <i class="la la-image font-large-4 text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Details Column -->
                                <div class="col-lg-8 col-md-7">
                                    <h2 class="text-bold-600 mb-1">{{ $event->title }}</h2>
                                    <p class="text-muted mb-2"><i class="la la-map-marker danger"></i> {{ $event->venue }}
                                    </p>

                                    <div class="row mb-2">
                                    <div class="col-sm-6 col-md-4 mb-1">
                                        <div class="p-1 border rounded border-primary bg-primary text-white text-left">
                                            <i class="la la-calendar font-medium-3 mr-1"></i>
                                            <span class="font-medium-2">{{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('d M, Y') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-1">
                                        <div class="p-1 border rounded border-success bg-success text-white text-left">
                                            <i class="la la-clock-o font-medium-3 mr-1"></i>
                                            <span class="font-medium-2">{{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('h:i A') : 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-md-4 mb-1">
                                        <div class="p-1 border rounded border-info bg-info text-white text-left">
                                            <i class="la la-money font-medium-3 mr-1"></i>
                                            <span class="font-medium-2">{{ $event->price }} {{ __('admin.currency') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <h5 class="text-bold-500">{{ __('admin.categories.description') }}</h5>
                                    <p class="text-muted">{{ Str::limit($event->description ?? 'No description', 250) }}</p>
                                </div>

                                <div class="progress-wrapper">
                                    <div class="d-flex justify-content-between mb-0.5">
                                        <small>{{ __('admin.events.capacity') }}</small>
                                        <small class="text-bold-600">{{ $event->tickets_sold }} / {{ $event->capacity }}</small>
                                    </div>
                                    <div class="progress progress-sm">
                                        @php 
                                            $percent = $event->capacity > 0 ? ($event->tickets_sold / $event->capacity) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 pb-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title text-bold-600">
                        <i class="la la-ticket"></i> {{ __('admin.bookings.title') }}
                        <span class="badge badge-pill badge-secondary ml-1">{{ $bookings->total() }}</span>
                    </h4>
                    
                    <form action="{{ route('admin.events.show', $event->id) }}" method="GET" class="form-inline">
                        <div class="form-group position-relative has-icon-left mb-0">
                            <input type="text" class="form-control form-control-sm" name="search" placeholder="{{ __('admin.buttons.actions') }}..." value="{{ request('search') }}">
                            <div class="form-control-position">
                                <i class="la la-search"></i>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-top-0">#</th>
                                        <th class="border-top-0">{{ __('admin.bookings.ticket_number') }}</th>
                                        <th class="border-top-0">{{ __('admin.bookings.name') }}</th>
                                        <th class="border-top-0">{{ __('admin.bookings.ticket_type') }}</th>
                                        <th class="border-top-0">{{ __('admin.bookings.seats') }}</th>
                                        <th class="border-top-0">{{ __('admin.bookings.status') }}</th>
                                        <th class="border-top-0">{{ __('admin.bookings.date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                        @forelse($bookings as $booking)
                                            <tr>
                                                <td class="text-muted">{{ $loop->iteration }}</td>
                                                <td class="text-bold-600 text-info">#{{ $booking->ticket_number }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-sm mr-1 bg-light-primary">
                                                            <span class="avatar-content text-primary font-small-3">{{ strtoupper(substr($booking->name ?? $booking->user->name ?? 'U', 0, 2)) }}</span>
                                                        </div>
                                                        <div>
                                                            <div class="text-bold-600 font-small-3">{{ $booking->name ?? $booking->user->name ?? 'Guest' }}</div>
                                                            <div class="text-muted font-small-2">{{ $booking->email ?? $booking->user->email ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($booking->ticket_type == 'vip')
                                                        <span class="badge badge-glow badge-warning rounded-pill px-1">VIP</span>
                                                    @else
                                                        <span class="badge badge-light-secondary rounded-pill px-1">Regular</span>
                                                    @endif
                                                </td>
                                                <td class="text-center font-weight-bold">{{ $booking->seats }}</td>
                                            <td>

                                                                                                @if($booking->status == 'confirmed')
                                                                                                    <span
                                                                                                        class="badge badge-success bg-lighten-4 text-success border border-success rounded px-1">{{ $booking->status }}</span>
                                                                                                @elseif($booking->status == 'pending')
                                                        <span
                                                            class="badge badge-warning bg-lighten-4 text-warning border border-warning rounded px-1">{{ $booking->status }}</span>
                                                    @else
                                                        <span class="badge badge-danger bg-lighten-4 text-danger border border-danger rounded px-1">{{ $booking->status }}</span>
                                                    @endif

                                                                                            </td>
                                                <td class="text-muted font-small-3">{{ $booking->created_at->format('Y-m-d h:i A') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3">
                                                    <div class="text-muted">
                                                        <i class="la la-inbox font-large-2 d-block mb-1"></i>
                                                        {{ __('admin.categories.no_records') }}
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2">
                                {{ $bookings->links() }}
                            </div>
                        </div>
        </div>
                </div>

            </div>
        </div>
    </div>
@endsection