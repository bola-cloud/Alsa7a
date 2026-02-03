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
                                href="{{ route('admin.clubs.index') }}">{{ __('admin.clubs.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ $club->name }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ $club->name }}</h3>
        </div>
    </div>

    <!-- Banner & Logo Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-content">
            <div class="card-body p-0 position-relative">
                @if($club->banner_url)
                    <div
                        style="height: 250px; background: url('{{ $club->banner_url }}') center center / cover no-repeat; border-radius: 8px 8px 0 0;">
                    </div>
                @else
                    <div class="bg-light" style="height: 200px; border-radius: 8px 8px 0 0;"></div>
                @endif

                <div class="position-absolute" style="bottom: -50px; left: 30px;">
                    <img src="{{ $club->logo_url }}" class="rounded-circle border-white border-4 shadow" width="120"
                        height="120" style="border: 5px solid #fff; background: #fff; object-fit: contain;">
                </div>

                <div class="float-right p-3 mt-2">
                    @if($club->is_featured)
                        <span class="badge badge-warning badge-pill p-2"><i class="la la-star"></i>
                            {{ __('admin.clubs.featured') }}</span>
                    @endif
                    <a href="{{ route('admin.clubs.edit', $club->id) }}" class="btn btn-outline-primary round ml-2">
                        <i class="la la-edit"></i> {{ __('admin.buttons.edit') }}
                    </a>
                </div>
            </div>
            <div class="card-body pt-5 pl-4 ml-2 mt-2 pb-4">
                <div class="row">
                    <div class="col-md-8">
                        <h2 class="font-weight-bold text-primary">{{ $club->name }}</h2>
                        <p class="text-muted"><i class="la la-map-marker text-danger"></i> {{ $club->city }},
                            {{ $club->country }}
                        </p>
                        <p class="mt-3">{{ $club->description }}</p>

                        <div class="mt-4">
                            <h5 class="text-muted small text-uppercase font-weight-bold mb-2">{{ __('admin.menu.sports') }}
                            </h5>
                            @foreach($club->sports as $sport)
                                <span class="badge badge-primary badge-pill mr-1">{{ $sport->name }}</span>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <h5 class="text-muted small text-uppercase font-weight-bold mb-2">
                                {{ __('admin.leagues.index') }}
                            </h5>
                            @foreach($club->leagues as $league)
                                <span class="badge badge-info badge-pill mr-1">{{ $league->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-light p-3 rounded">
                            <h6 class="font-weight-bold mb-3 border-bottom pb-2">
                                {{ __('admin.clubs.details') ?? 'Club Details' }}
                            </h6>
                            <p class="mb-2"><strong>{{ __('admin.clubs.founded_year') }}:</strong> <span
                                    class="float-right">{{ $club->founded_year }}</span></p>
                            <p class="mb-2"><strong>{{ __('admin.clubs.website') }}:</strong> <span class="float-right"><a
                                        href="{{ $club->website }}" target="_blank"
                                        class="text-info">{{ parse_url($club->website, PHP_URL_HOST) ?: $club->website }}</a></span>
                            </p>
                            <p class="mb-0"><strong>{{ __('admin.clubs.owner') }}:</strong> <span
                                    class="float-right text-primary font-weight-bold">{{ $club->owner->name ?? '-' }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teams & Roster Section -->
    <div class="row">
        <!-- Teams Section -->
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom pt-4 d-flex justify-content-between align-items-center">
                    <h4 class="card-title font-weight-bold text-primary mb-0"><i class="la la-group"></i>
                        {{ __('admin.clubs.teams') }}</h4>
                    <a href="{{ route('admin.clubs.teams.index', $club->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="la la-cog"></i> {{ __('admin.buttons.manage') }}
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($club->teams as $team)
                            <div class="col-md-6 mb-4">
                                <div class="border rounded p-3 h-100 bg-light-blue shadow-none border-light">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($team->image)
                                            <img src="{{ url('storage/' . $team->image) }}" class="rounded mr-2" width="50"
                                                height="50" style="object-fit: cover;">
                                        @else
                                            <div class="bg-white rounded mr-2 d-flex align-items-center justify-content-center border"
                                                style="width: 50px; height: 50px;">
                                                <i class="la la-shield font-large-1 text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h5 class="font-weight-bold mb-0">{{ $team->name }}</h5>
                                            <small class="text-info">{{ $team->age_group }}</small>
                                        </div>
                                    </div>

                                    <h6 class="small font-weight-bold text-muted text-uppercase mb-2">
                                        {{ __('admin.clubs.members') }} ({{ $team->members->count() }})
                                    </h6>
                                    <div class="d-flex flex-wrap">
                                        @foreach($team->members->take(8) as $member)
                                            <div class="text-center mr-3 mb-2" style="width: 60px;">
                                                <img src="{{ $member->profile_photo_url }}" class="rounded-circle border" width="40"
                                                    height="40" title="{{ $member->name }}">
                                                <div class="small text-truncate" style="font-size: 10px;">{{ $member->name }}</div>
                                            </div>
                                        @endforeach
                                        @if($team->members->count() > 8)
                                            <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center mb-2"
                                                style="width: 40px; height: 40px;">
                                                <span class="small font-weight-bold">+{{ $team->members->count() - 8 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4 text-muted">
                                {{ __('admin.categories.no_records') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Roster Section -->
        <div class="col-md-12 mt-2">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom pt-4">
                    <h4 class="card-title font-weight-bold text-primary"><i class="la la-users"></i>
                        {{ __('admin.clubs.roster') }}</h4>
                </div>
                <div class="card-body">
                    <div class="accordion" id="rosterAccordion">
                        @forelse($roster as $category => $members)
                            <div class="card collapse-icon accordion-icon-rotate border-bottom mb-0 shadow-none">
                                <div class="card-header" id="heading{{ Str::slug($category) }}" data-toggle="collapse"
                                    data-target="#collapse{{ Str::slug($category) }}" aria-expanded="true"
                                    aria-controls="collapse{{ Str::slug($category) }}" style="cursor: pointer;">
                                    <h5 class="mb-0">
                                        <span class="font-weight-bold text-primary">{{ $category }}</span>
                                        <span class="badge badge-pill badge-light-primary float-right">{{ $members->count() }}</span>
                                    </h5>
                                </div>

                                <div id="collapse{{ Str::slug($category) }}" class="collapse {{ $loop->first ? 'show' : '' }}"
                                    aria-labelledby="heading{{ Str::slug($category) }}" data-parent="#rosterAccordion">
                                    <div class="card-body pl-0 pr-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('admin.users.name') }}</th>
                                                        <th>{{ __('admin.users.email') }}</th>
                                                        <th>{{ __('admin.clubs.position') }}</th>
                                                        <th class="text-right">{{ __('admin.buttons.actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($members as $member)
                                                        <tr>
                                                            <td class="align-middle">
                                                                <div class="d-flex align-items-center">
                                                                    @if($member->profile_photo_url)
                                                                        <img src="{{ $member->profile_photo_url }}" class="rounded-circle mr-2"
                                                                            width="35" height="35" style="object-fit: cover;">
                                                                    @else
                                                                        <span class="avatar avatar-sm bg-light text-primary mr-2">
                                                                            <span class="avatar-content">{{ Str::substr($member->name, 0, 1) }}</span>
                                                                        </span>
                                                                    @endif
                                                                    <span class="font-weight-bold text-dark">{{ $member->name }}</span>
                                                                </div>
                                                            </td>
                                                            <td class="align-middle small">{{ $member->email }}</td>
                                                            <td class="align-middle">
                                                                @if($member->position)
                                                                    <span class="badge badge-primary">{{ $member->position }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="align-middle text-right">
                                                                <a href="{{ route('admin.users.show', $member->id) }}"
                                                                    class="btn btn-sm btn-icon btn-white text-info"
                                                                    title="{{ __('admin.buttons.view') }}">
                                                                    <i class="la la-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                {{ __('admin.categories.no_records') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection