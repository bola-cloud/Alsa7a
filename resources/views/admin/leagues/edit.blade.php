@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.leagues.edit') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.leagues.index') }}">{{ __('admin.leagues.index') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.leagues.edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.leagues.update', $league->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.name') }} (EN)</label>
                                    <input type="text" name="name[en]" class="form-control" required
                                        value="{{ old('name.en', $league->name_en) }}">
                                </div>
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.name') }} (AR)</label>
                                    <input type="text" name="name[ar]" class="form-control" required
                                        value="{{ old('name.ar', $league->name_ar) }}">
                                </div>
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.description') }} (EN)</label>
                                    <textarea name="description[en]" class="form-control"
                                        rows="3">{{ old('description.en', $league->description_en) }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.description') }} (AR)</label>
                                    <textarea name="description[ar]" class="form-control"
                                        rows="3">{{ old('description.ar', $league->description_ar) }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.sports.index') }}</label>
                                    <select name="sport_id" class="form-control" required>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}" {{ $league->sport_id == $sport->id ? 'selected' : '' }}>{{ $sport->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.season') }}</label>
                                    <input type="text" name="season" class="form-control"
                                        value="{{ old('season', $league->season) }}">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.leagues.start_date') }}</label>
                                            <input type="date" name="start_date" class="form-control"
                                                value="{{ old('start_date', $league->start_date) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.leagues.end_date') }}</label>
                                            <input type="date" name="end_date" class="form-control"
                                                value="{{ old('end_date', $league->end_date) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.leagues.image') }}</label>
                                    @if($league->image)
                                        <div class="mb-2">
                                            <img src="{{ $league->image }}" style="height: 60px" class="img-thumbnail">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="image" id="leagueImage">
                                        <label class="custom-file-label" for="leagueImage">Choose file</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.sports.active') }}</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1" {{ $league->is_active ? 'selected' : '' }}>
                                            {{ __('admin.categories.yes') }}
                                        </option>
                                        <option value="0" {{ !$league->is_active ? 'selected' : '' }}>
                                            {{ __('admin.categories.no') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions text-right">
                            <a href="{{ route('admin.leagues.index') }}" class="btn btn-warning mr-1">
                                <i class="la la-remove"></i> {{ __('admin.buttons.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-check"></i> {{ __('admin.buttons.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection