@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.market_requests.title') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.market_requests.title') }}</h3>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('admin.market_requests.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.categories.search') }}</label>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control pl-4"
                                placeholder="{{ __('admin.market_requests.job_title') }} / {{ __('admin.market_requests.poster') }}..."
                                value="{{ request('search') }}">
                            <i class="la la-search position-absolute" style="top: 10px; left: 10px; color: #b0afb5;"></i>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.market_requests.category') }}</label>
                        <select name="category_id" class="form-control">
                            <option value="">{{ __('admin.market_requests.all_categories') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() == 'ar' ? $category->name_ar : $category->name_en }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.market_requests.status') }}</label>
                        <select name="status" class="form-control">
                            <option value="">{{ __('admin.market_requests.all_statuses') }}</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('admin.market_requests.active') }}</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>{{ __('admin.market_requests.closed') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
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
                                <th class="border-top-0">{{ __('admin.market_requests.job_title') }}</th>
                                <th class="border-top-0">{{ __('admin.market_requests.poster') }}</th>
                                <th class="border-top-0">{{ __('admin.market_requests.category') }}</th>
                                <th class="border-top-0 text-center">{{ __('admin.market_requests.applicants_count') }}</th>
                                <th class="border-top-0">{{ __('admin.market_requests.status') }}</th>
                                <th class="border-top-0">{{ __('admin.market_requests.date') }}</th>
                                <th class="border-top-0 text-right">{{ __('admin.buttons.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $marketRequest)
                                <tr>
                                    <td class="align-middle">{{ $loop->iteration }}</td>
                                    <td class="align-middle font-weight-bold">{{ $marketRequest->title }}</td>
                                    <td class="align-middle">
                                        {{ $marketRequest->user->name ?? '-' }}
                                        @if($marketRequest->club)
                                            <br><small class="text-muted">{{ $marketRequest->club->name }}</small>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        {{ $marketRequest->category ? (app()->getLocale() == 'ar' ? $marketRequest->category->name_ar : $marketRequest->category->name_en) : '-' }}
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="badge badge-pill badge-light border">{{ $marketRequest->applications_count }}</span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-pill badge-{{ $marketRequest->status === 'active' ? 'success' : 'secondary' }} px-3 py-1">
                                            {{ $marketRequest->status === 'active' ? __('admin.market_requests.active') : __('admin.market_requests.closed') }}
                                        </span>
                                    </td>
                                    <td class="align-middle">{{ $marketRequest->created_at?->format('Y-m-d') }}</td>
                                    <td class="align-middle text-right">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.market_requests.show', $marketRequest->id) }}"
                                                class="btn btn-sm btn-outline-info round"
                                                title="{{ __('admin.buttons.view') }}">
                                                <i class="la la-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.market_requests.update', $marketRequest->id) }}" method="POST"
                                                style="display:inline-block"
                                                onsubmit="return confirm('{{ $marketRequest->status === 'active' ? __('admin.market_requests.confirm_close') : __('admin.market_requests.confirm_reopen') }}');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="{{ $marketRequest->status === 'active' ? 'closed' : 'active' }}">
                                                <button type="submit"
                                                    class="btn btn-sm {{ $marketRequest->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} round ml-1"
                                                    title="{{ $marketRequest->status === 'active' ? __('admin.market_requests.close') : __('admin.market_requests.reopen') }}">
                                                    <i class="la {{ $marketRequest->status === 'active' ? 'la-lock' : 'la-unlock' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.market_requests.destroy', $marketRequest->id) }}" method="POST"
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
                                    <td colspan="8" class="text-center p-3 text-muted">
                                        {{ __('admin.market_requests.no_records') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-2">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
