@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title text-bold-700">{{ __('admin.sliders.index') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <a href="{{ route('admin.sliders.create') }}" class="btn btn-primary">
                    <i class="la la-plus"></i> {{ __('admin.buttons.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($sliders as $slider)
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="modern-card h-100">
                    <div class="card-img-top-wrapper" style="height: 200px;">
                        @if($slider->image)
                            <img src="{{ Str::startsWith($slider->image_url, 'http') ? $slider->image_url : asset('storage/' . $slider->image) }}"
                                class="card-img-top" alt="{{ $slider->title }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-secondary text-white">
                                <i class="la la-image font-large-3"></i>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">{{ $slider->title }}</h5>

                        <div class="card-actions">
                            <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="btn btn-sm btn-outline-primary"
                                title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i>
                            </a>

                            <form action="{{ route('admin.sliders.destroy', $slider->id) }}" method="POST"
                                onsubmit="return confirm('{{ __('admin.messages.confirm_delete') }}');">
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