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
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.clubs.teams.index', $club->id) }}">{{ $club->name }} -
                                {{ __('admin.teams.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.buttons.edit') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.buttons.edit') }}</h3>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.buttons.edit') }} {{ __('admin.teams.title') }}: {{ $team->name }}
                    </h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.clubs.teams.update', [$club->id, $team->id]) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.teams.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required
                                        value="{{ old('name', $team->name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.teams.short_name') }}</label>
                                    <input type="text" name="short_name" class="form-control"
                                        value="{{ old('short_name', $team->short_name) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.teams.sport') }} <span class="text-danger">*</span></label>
                                    <select name="sport_id" class="form-control select2" required>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}" {{ $team->sport_id == $sport->id ? 'selected' : '' }}>{{ $sport->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.teams.age_group') }}</label>
                                    <input type="text" name="age_group" class="form-control"
                                        value="{{ old('age_group', $team->age_group) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('admin.teams.jersey_color') }}</label>
                                    <input type="text" name="jersey_color" class="form-control"
                                        value="{{ old('jersey_color', $team->jersey_color) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('admin.teams.coach') }}</label>
                                    <input type="text" name="coach" class="form-control"
                                        value="{{ old('coach', $team->coach) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('admin.teams.founded_year') }}</label>
                                    <input type="number" name="founded_year" class="form-control"
                                        value="{{ old('founded_year', $team->founded_year) }}">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{ __('admin.teams.image') }}</label>
                                    @if($team->image)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $team->image) }}" height="60" class="rounded">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" name="image" class="custom-file-input" id="teamImage">
                                        <label class="custom-file-label"
                                            for="teamImage">{{ __('admin.settings.choose_file') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="active" id="active" value="1"
                                        {{ $team->active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="active">{{ __('admin.teams.active') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions mt-4 text-right">
                            <a href="{{ route('admin.clubs.teams.index', $club->id) }}" class="btn btn-warning mr-2"><i
                                    class="la la-times"></i> {{ __('admin.buttons.cancel') }}</a>
                            <button type="submit" class="btn btn-primary"><i class="la la-check-circle"></i>
                                {{ __('admin.buttons.update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection