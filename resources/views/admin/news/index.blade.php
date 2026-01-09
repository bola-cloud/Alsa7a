@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.news.title') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.news.title') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12 text-right">
            <a href="{{ route('admin.news.create') }}" class="btn btn-primary shadow-soft">
                <i class="la la-plus"></i> {{ __('admin.buttons.add_new') }}
            </a>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('admin.news.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.categories.search') }}</label>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control pl-4"
                                placeholder="{{ __('admin.news.title') }}..." value="{{ request('search') }}">
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
        @forelse($news as $item)
            <div class="col-xl-4 col-md-6 col-12 mb-4">
                <div class="admin-card h-100">
                    <div class="card-img-wrapper position-relative" style="height: 220px; padding: 0;">
                        @if($item->featured_image)
                            <img src="{{ $item->featured_image }}" class="card-img-top w-100 h-100" style="object-fit: cover;"
                                alt="{{ $item->title }}">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-light text-muted">
                                <i class="la la-image font-large-3"></i>
                            </div>
                        @endif

                        @if($item->sport)
                            <span class="badge badge-primary position-absolute" style="top: 10px; right: 10px; padding: 5px 10px;">
                                {{ $item->sport->name }}
                            </span>
                        @endif
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-1">
                            {{ Str::limit($item->title, 50) }}
                        </h5>
                        <p class="text-muted small mb-2">
                            <i class="la la-calendar"></i> {{ $item->created_at->format('Y-m-d') }}
                        </p>

                        <p class="card-text text-muted mb-4">
                            {{ Str::limit(strip_tags($item->content), 100) }}
                        </p>

                        <div class="mt-auto pt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge badge-light text-muted">#{{ $item->id }}</span>
                                <div class="btn-group">
                                    <a href="{{ route('admin.news.edit', $item->id) }}"
                                        class="btn btn-sm btn-outline-primary round" title="{{ __('admin.buttons.edit') }}">
                                        <i class="la la-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.news.destroy', $item->id) }}" method="POST"
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
                    <div class="card-body text-center p-5">
                        <div class="mb-2">
                            <i class="la la-newspaper-o font-large-3 text-muted"></i>
                        </div>
                        <h4 class="text-muted">{{ __('admin.categories.no_records') }}</h4>
                        <a href="{{ route('admin.news.create') }}" class="btn btn-primary mt-2">
                            {{ __('admin.buttons.add_new') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="row">
        <div class="col-12">
            {{ $news->links() }}
        </div>
    </div>
@endsection