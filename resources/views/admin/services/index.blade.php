@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.services.title') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.services.title') }}</h3>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('admin.services.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.categories.search') }}</label>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control pl-4"
                                placeholder="{{ __('admin.services.title') }} / {{ __('admin.services.provider') }}..."
                                value="{{ request('search') }}">
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

    <div class="card shadow-sm border-0">
        <div class="card-content collapse show">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-primary white">
                            <tr>
                                <th class="border-top-0">#</th>
                                <th class="border-top-0">{{ __('admin.services.title') }}</th>
                                <th class="border-top-0">{{ __('admin.services.provider') }}</th>
                                <th class="border-top-0">{{ __('admin.services.price') }}</th>
                                <th class="border-top-0">{{ __('admin.sports.active') }}</th>
                                <th class="border-top-0">{{ __('admin.services.featured') }}</th>
                                <th class="border-top-0 text-right">{{ __('admin.buttons.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                                <tr>
                                    <td class="align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle font-weight-bold">
                                        {{ $service->title }}
                                        @if($service->sport)<br><small
                                        class="text-muted">{{ $service->sport->name }}</small>@endif
                                    </td>
                                    <td class="align-middle">{{ $service->provider->name ?? '-' }}</td>
                                    <td class="align-middle">{{ $service->price }} <small>{{ $service->currency }}</small></td>
                                    <td class="align-middle">
                                        <form action="{{ route('admin.services.toggle', $service->id) }}" method="POST"
                                            style="display:inline-block">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm {{ $service->is_active ? 'btn-success' : 'btn-danger' }} round"
                                                title="{{ __('admin.buttons.toggle') }}">
                                                {{ $service->is_active ? __('admin.categories.yes') : __('admin.categories.no') }}
                                            </button>
                                        </form>
                                    <td class="align-middle text-center">
                                        <form action="{{ route('admin.services.toggle_featured', $service->id) }}" method="POST"
                                            style="display:inline-block">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm {{ $service->is_featured ? 'btn-warning' : 'btn-outline-warning' }} round"
                                                title="{{ __('admin.services.featured') }}">
                                                <i class="la {{ $service->is_featured ? 'la-star' : 'la-star-o' }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="align-middle text-right">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.services.show', $service->id) }}"
                                                class="btn btn-sm btn-outline-info round"
                                                title="{{ __('admin.buttons.view') }}">
                                                <i class="la la-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST"
                                                style="display:inline-block"
                                                onsubmit="return confirm('{{ __('admin.buttons.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger round ml-1"
                                                    title="{{ __('admin.buttons.delete') }}">
                                                    <i class="la la-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center p-3 text-muted">
                                        {{ __('admin.categories.no_records') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-2">
                    {{ $services->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection