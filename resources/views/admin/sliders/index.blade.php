@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.sliders.index') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.sliders.index') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12 text-right">
            <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary shadow-soft">
                <i class="la la-plus"></i> {{ __('admin.buttons.add_new') }}
            </a>
        </div>
    </div>

    <div class="row">
        @forelse($sliders as $slider)
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="admin-card">
                    <div class="card-img-wrapper" style="height: 160px; padding: 0;">
                        @if($slider->image)
                            <img src="{{ Str::startsWith($slider->image_url, 'http') ? $slider->image_url : asset('storage/' . $slider->image) }}"
                                class="card-img-top w-100 h-100" style="object-fit: cover;" alt="{{ $slider->title }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-light text-muted">
                                <i class="la la-image font-large-3"></i>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="card-title text-truncate" title="{{ $slider->title }}">
                            {{ $slider->title ?: 'No Title' }}
                        </h5>
                        <p class="card-text small text-muted">
                            {{ Str::limit($slider->description ?? '', 80) }}
                        </p>

                        <div class="card-actions">
                            <a href="{{ route('admin.sliders.edit', $slider->id) }}"
                                class="btn btn-sm btn-outline-primary round" title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i> {{ __('admin.buttons.edit') }}
                            </a>

                            <form action="{{ route('admin.sliders.destroy', $slider->id) }}" method="POST"
                                onsubmit="return confirm('{{ __('admin.messages.confirm_delete') }}');"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger round border-0"
                                    title="{{ __('admin.buttons.delete') }}">
                                    <i class="la la-trash font-medium-3"></i>
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
                        <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary mt-2">
                            {{ __('admin.buttons.add_new') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="row">
        <div class="col-12">
            {{ $sliders->links() }}
        </div>
    </div>
@endsection