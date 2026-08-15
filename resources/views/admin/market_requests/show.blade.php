@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.market_requests.index') }}">{{ __('admin.market_requests.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($marketRequest->title, 20) }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.market_requests.title') }}</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="bg-light rounded-circle mb-3 d-flex align-items-center justify-content-center mx-auto"
                        style="width: 70px; height: 70px;">
                        <i class="la la-suitcase font-large-2 text-muted"></i>
                    </div>

                    <h4 class="card-title font-weight-bold">{{ $marketRequest->title }}</h4>
                    <p class="text-muted mb-2">
                        {{ $marketRequest->category ? (app()->getLocale() == 'ar' ? $marketRequest->category->name_ar : $marketRequest->category->name_en) : '-' }}
                    </p>

                    <div class="mb-3">
                        <span class="badge badge-pill badge-{{ $marketRequest->status === 'active' ? 'success' : 'secondary' }} px-3 py-1">
                            {{ $marketRequest->status === 'active' ? __('admin.market_requests.active') : __('admin.market_requests.closed') }}
                        </span>
                    </div>

                    <div class="row text-left mt-4">
                        <div class="col-12 py-2 border-bottom">
                            <strong>{{ __('admin.market_requests.poster') }}:</strong>
                            <span class="float-right">{{ $marketRequest->user->name ?? '-' }}</span>
                        </div>
                        <div class="col-12 py-2 border-bottom">
                            <strong>{{ __('admin.market_requests.club') }}:</strong>
                            <span class="float-right">{{ $marketRequest->club->name ?? __('admin.market_requests.no_club') }}</span>
                        </div>
                        <div class="col-12 py-2 border-bottom">
                            <strong>{{ __('admin.market_requests.applicants_count') }}:</strong>
                            <span class="float-right">{{ $marketRequest->applications->count() }}</span>
                        </div>
                        <div class="col-12 py-2 border-bottom">
                            <strong>{{ __('admin.market_requests.date') }}:</strong>
                            <span class="float-right">{{ $marketRequest->created_at?->format('Y-m-d H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h4 class="card-title">{{ __('admin.market_requests.description') }}</h4>
                </div>
                <div class="card-body">
                    <p class="text-dark">{{ $marketRequest->description }}</p>
                </div>
                <div class="card-footer bg-white border-top-0 text-right pb-4">
                    <a href="{{ route('admin.market_requests.index') }}" class="btn btn-outline-secondary mr-2">
                        <i class="la la-arrow-left"></i> {{ __('admin.buttons.back') }}
                    </a>
                    <form action="{{ route('admin.market_requests.update', $marketRequest->id) }}" method="POST"
                        style="display:inline-block"
                        onsubmit="return confirm('{{ $marketRequest->status === 'active' ? __('admin.market_requests.confirm_close') : __('admin.market_requests.confirm_reopen') }}');">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="{{ $marketRequest->status === 'active' ? 'closed' : 'active' }}">
                        <button type="submit" class="btn {{ $marketRequest->status === 'active' ? 'btn-warning' : 'btn-success' }}">
                            <i class="la {{ $marketRequest->status === 'active' ? 'la-lock' : 'la-unlock' }}"></i>
                            {{ $marketRequest->status === 'active' ? __('admin.market_requests.close') : __('admin.market_requests.reopen') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h4 class="card-title">
                        {{ __('admin.market_requests.applicants') }}
                        <span class="badge badge-light border">{{ $marketRequest->applications->count() }}</span>
                    </h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>{{ __('admin.market_requests.applicant') }}</th>
                                    <th>{{ __('admin.market_requests.notes') }}</th>
                                    <th>{{ __('admin.market_requests.cv') }}</th>
                                    <th>{{ __('admin.market_requests.applied_at') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($marketRequest->applications as $application)
                                    <tr>
                                        <td class="align-middle font-weight-bold">
                                            {{ $application->user->name ?? '-' }}
                                            @if($application->user)
                                                <br><small class="text-muted">{{ $application->user->phone }}</small>
                                            @endif
                                        </td>
                                        <td class="align-middle">{{ $application->notes ?: '-' }}</td>
                                        <td class="align-middle">
                                            @if($application->cv_path)
                                                <a href="{{ asset('storage/' . $application->cv_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary round">
                                                    <i class="la la-file-pdf-o"></i> {{ __('admin.market_requests.view_cv') }}
                                                </a>
                                            @else
                                                <span class="text-muted small">{{ __('admin.market_requests.no_cv') }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">{{ $application->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-3 text-muted">
                                            {{ __('admin.market_requests.no_applicants') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
