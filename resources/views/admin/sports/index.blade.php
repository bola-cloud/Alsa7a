@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title text-bold-700">{{ __('admin.sports.index') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <a href="{{ route('admin.sports.create') }}" class="btn btn-primary">
                    <i class="la la-plus"></i> {{ __('admin.buttons.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($sports as $sport)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="modern-card h-100">
                    {{-- Status Badge --}}
                    @if($sport->active)
                        <span class="status-badge bg-success">{{ __('admin.categories.yes') }}</span>
                    @else
                        <span class="status-badge bg-danger">{{ __('admin.categories.no') }}</span>
                    @endif

                    {{-- Image/Icon Area --}}
                    <div class="card-img-top-wrapper"
                        style="height: 120px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);">
                        @if($sport->icon_url)
                            <img src="{{ Str::startsWith($sport->icon_url, 'http') ? $sport->icon_url : asset('storage/' . $sport->icon_url) }}"
                                alt="{{ $sport->name }}" style="height: 60px; width: 60px; object-fit: contain;">
                        @else
                            <i class="la la-trophy font-large-3 text-muted"></i>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">{{ $sport->name }}</h5>
                        <p class="text-muted small">
                            {{ Str::limit($sport->description, 50) }}
                        </p>

                        <div class="card-actions">
                            <a href="{{ route('admin.sports.edit', $sport->id) }}" class="btn btn-sm btn-outline-primary"
                                title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i>
                            </a>

                            <form action="{{ route('admin.sports.destroy', $sport->id) }}" method="POST"
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
                        <a href="{{ route('admin.sports.create') }}" class="btn btn-primary mt-2">
                            {{ __('admin.buttons.add_new') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="row">
        <div class="col-12">
            {{ $sports->links() }}
        </div>
    </div>
@endsection