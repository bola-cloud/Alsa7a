@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-xl-4 col-lg-6 col-12">
            <a href="{{ route('admin.users.index') }}" class="d-block" style="text-decoration: none; color: inherit;">
                <div class="card pull-up">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="info">{{ $stats['users'] }}</h3>
                                    <h6>{{ __('admin.dashboard.users') }}</h6>
                                </div>
                                <div>
                                    <i class="icon-users info font-large-2 float-right"></i>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                <div class="progress-bar bg-gradient-x-info" role="progressbar"
                                    style="width: {{ $percentages['users'] }}%" aria-valuenow="{{ $percentages['users'] }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
            <a href="{{ route('admin.services.index') }}" class="d-block" style="text-decoration: none; color: inherit;">
                <div class="card pull-up">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="warning">{{ $stats['services'] }}</h3>
                                    <h6>{{ __('admin.dashboard.services') }}</h6>
                                </div>
                                <div>
                                    <i class="icon-briefcase warning font-large-2 float-right"></i>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                <div class="progress-bar bg-gradient-x-warning" role="progressbar"
                                    style="width: {{ $percentages['services'] }}%"
                                    aria-valuenow="{{ $percentages['services'] }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
            <a href="{{ route('admin.service_requests.index') }}" class="d-block"
                style="text-decoration: none; color: inherit;">
                <div class="card pull-up">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="success">{{ $stats['requests'] }}</h3>
                                    <h6>{{ __('admin.dashboard.requests') }}</h6>
                                </div>
                                <div>
                                    <i class="icon-basket-loaded success font-large-2 float-right"></i>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                <div class="progress-bar bg-gradient-x-success" role="progressbar"
                                    style="width: {{ $percentages['requests'] }}%"
                                    aria-valuenow="{{ $percentages['requests'] }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
            <a href="{{ route('admin.posts.index') }}" class="d-block" style="text-decoration: none; color: inherit;">
                <div class="card pull-up">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="danger">{{ $stats['posts'] }}</h3>
                                    <h6>{{ __('admin.dashboard.posts') }}</h6>
                                </div>
                                <div>
                                    <i class="icon-camera danger font-large-2 float-right"></i>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                <div class="progress-bar bg-gradient-x-danger" role="progressbar"
                                    style="width: {{ $percentages['posts'] }}%" aria-valuenow="{{ $percentages['posts'] }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
            <a href="{{ route('admin.news.index') }}" class="d-block" style="text-decoration: none; color: inherit;">
                <div class="card pull-up">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="info">{{ $stats['news'] }}</h3>
                                    <h6>{{ __('admin.dashboard.news') }}</h6>
                                </div>
                                <div>
                                    <i class="icon-book-open info font-large-2 float-right"></i>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                <div class="progress-bar bg-gradient-x-info" role="progressbar"
                                    style="width: {{ $percentages['news'] }}%" aria-valuenow="{{ $percentages['news'] }}"
                                    aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-4 col-lg-6 col-12">
            <a href="{{ route('admin.tickets.index') }}" class="d-block" style="text-decoration: none; color: inherit;">
                <div class="card pull-up">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="media-body text-left">
                                    <h3 class="warning">{{ $stats['tickets'] }}</h3>
                                    <h6>{{ __('admin.dashboard.tickets') }}</h6>
                                </div>
                                <div>
                                    <i class="icon-support warning font-large-2 float-right"></i>
                                </div>
                            </div>
                            <div class="progress progress-sm mt-1 mb-0 box-shadow-2">
                                <div class="progress-bar bg-gradient-x-warning" role="progressbar"
                                    style="width: {{ $percentages['tickets'] }}%"
                                    aria-valuenow="{{ $percentages['tickets'] }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.dashboard.recent_activity') }}</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        <p class="card-text">{{ __('admin.dashboard.welcome') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection