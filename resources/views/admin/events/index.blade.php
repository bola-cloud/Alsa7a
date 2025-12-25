@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title text-bold-700">{{ __('admin.events.index') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                    <i class="la la-plus"></i> {{ __('admin.buttons.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($events as $event)
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="modern-card h-100">
                    <div class="card-img-top-wrapper" style="height: 180px;">
                        @if($event->featured_image)
                            <img src="{{ $event->featured_image }}" class="card-img-top" alt="{{ $event->title }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-secondary text-white">
                                <i class="la la-calendar font-large-3"></i>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">{{ $event->title }}</h5>
                        <p class="text-muted small mb-1">
                            <i class="la la-map-marker"></i> {{ $event->venue ?? 'N/A' }}
                        </p>
                        <p class="text-muted small mb-1">
                            <i class="la la-clock-o"></i>
                            {{ $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('Y-m-d H:i') : 'N/A' }}
                        </p>
                        <p class="text-success font-weight-bold">
                            {{ $event->price > 0 ? number_format($event->price, 0) . ' OMR' : 'Free' }}
                        </p>

                        <div class="card-actions">
                            <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-sm btn-outline-info"
                                title="{{ __('admin.bookings.title') }}">
                                <i class="la la-eye"></i>
                            </a>

                            <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-sm btn-outline-primary"
                                title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i>
                            </a>

                            <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST"
                                onsubmit="return confirm('{{ __('admin.buttons.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    title="{{ __('admin.buttons.delete') }}">
                                    <i class="la la-trash"></i>
                                </button>
                            </form>
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