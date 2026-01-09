@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.sports.index') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.sports.index') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12 text-right">
            <a href="{{ route('admin.sports.create') }}" class="btn btn-primary shadow-soft">
                <i class="la la-plus"></i> {{ __('admin.buttons.add_new') }}
            </a>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('admin.sports.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.categories.search') }}</label>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control pl-4"
                                placeholder="{{ __('admin.sports.title') }}..." value="{{ request('search') }}">
                            <i class="la la-search position-absolute" style="top: 10px; left: 10px; color: #b0afb5;"></i>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.sports.active') }}</label>
                        <select name="active" class="form-control">
                            <option value="">{{ __('admin.categories.all') }}</option>
                            <option value="yes" {{ request('active') == 'yes' ? 'selected' : '' }}>
                                {{ __('admin.categories.yes') }}</option>
                            <option value="no" {{ request('active') == 'no' ? 'selected' : '' }}>
                                {{ __('admin.categories.no') }}</option>
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
        @forelse($sports as $sport)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="admin-card">
                    <div class="card-img-wrapper">
                        @if($sport->icon_url)
                            <img src="{{ $sport->icon_url }}" alt="{{ $sport->name }}" class="card-img-top"
                                style="max-height: 80px;">
                        @else
                            <i class="la la-trophy font-large-3 text-muted"></i>
                        @endif
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">{{ $sport->name }}</h5>
                            @if($sport->active)
                                <span class="badge badge-success badge-pill p-1">{{ __('admin.categories.yes') }}</span>
                            @else
                                <span class="badge badge-danger badge-pill p-1">{{ __('admin.categories.no') }}</span>
                            @endif
                        </div>

                        <p class="card-text text-muted small">
                            {{ Str::limit($sport->description, 60) }}
                        </p>

                        <div class="card-actions">
                            <a href="{{ route('admin.sports.edit', $sport->id) }}" class="btn btn-sm btn-outline-primary round"
                                title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i> {{ __('admin.buttons.edit') }}
                            </a>

                            <form action="{{ route('admin.sports.destroy', $sport->id) }}" method="POST"
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