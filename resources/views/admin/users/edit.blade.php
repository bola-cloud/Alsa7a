@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.users.edit') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.users.index') }}">{{ __('admin.users.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.buttons.edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form class="form" action="{{ route('admin.users.update', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-body">
                                <div class="form-group">
                                    <label>{{ __('admin.users.name') }}</label>
                                    <input type="text" class="form-control" name="name"
                                        value="{{ old('name', $user->name) }}" required>
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.users.email') }}</label>
                                    <input type="email" class="form-control" name="email"
                                        value="{{ old('email', $user->email) }}" required>
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.users.phone') }}</label>
                                    <input type="text" class="form-control" name="phone"
                                        value="{{ old('phone', $user->phone) }}" required>
                                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.users.password') }}
                                                ({{ __('admin.users.leave_blank') }})</label>
                                            <input type="password" class="form-control" name="password" minlength="8">
                                            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.users.confirm_password') }}</label>
                                            <input type="password" class="form-control" name="password_confirmation"
                                                minlength="8">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.users.role_category') }}</label>
                                    <select name="category_id" class="form-control select2">
                                        <option value="">User (Standard)</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $user->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="is_admin" id="isAdmin"
                                            value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                            for="isAdmin">{{ __('admin.users.is_admin') }}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="is_approved"
                                            id="isApproved" value="1" {{ old('is_approved', $user->is_approved) ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                            for="isApproved">{{ __('admin.users.is_approved') }}</label>
                                    </div>
                                </div>

                                <div class="mt-4 mb-3">
                                    <h5 class="text-muted text-uppercase small font-weight-bold"><i class="la la-users"></i>
                                        {{ __('admin.clubs.roster_details') }}</h5>
                                    <hr class="mt-1">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.clubs.title') }}</label>
                                            <select name="club_id" id="club_id" class="form-control select2">
                                                <option value="">{{ __('admin.buttons.select') }}</option>
                                                @foreach($clubs as $club)
                                                    <option value="{{ $club->id }}" {{ old('club_id', $user->club_id) == $club->id ? 'selected' : '' }}>
                                                        {{ $club->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.clubs.teams') }}</label>
                                            <select name="team_id" id="team_id" class="form-control select2">
                                                <option value="">{{ __('admin.buttons.select') }}</option>
                                                @foreach($teams as $team)
                                                    <option value="{{ $team->id }}" {{ old('team_id', $user->team_id) == $team->id ? 'selected' : '' }}>
                                                        {{ $team->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.clubs.position') }}</label>
                                            <input type="text" name="position" class="form-control"
                                                value="{{ old('position', $user->position) }}" placeholder="e.g. Forward">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.clubs.number') }}</label>
                                            <input type="text" name="number" class="form-control"
                                                value="{{ old('number', $user->number) }}" placeholder="e.g. 10">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions right">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-warning mr-1">
                                    <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="la la-check-square-o"></i> {{ __('admin.buttons.update') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#club_id').on('change', function () {
                var clubId = $(this).val();
                var teamSelect = $('#team_id');
                teamSelect.html('<option value="">Loading...</option>');

                if (clubId) {
                    // We'll use a simple fetch to get teams for this club
                    // Using a generic API endpoint if exists, or adding a quick route
                    $.get('/api/v1/clubs/' + clubId + '/teams', function (data) {
                        teamSelect.html('<option value="">{{ __("admin.buttons.select") }}</option>');
                        // Data might be under data.data depending on API structure
                        var teams = data.data || data;
                        $.each(teams, function (key, team) {
                            teamSelect.append('<option value="' + team.id + '">' + team.name + '</option>');
                        });
                    });
                } else {
                    teamSelect.html('<option value="">{{ __("admin.buttons.select") }}</option>');
                }
            });
        });
    </script>
@endpush