@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.leagues.create') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.leagues.index') }}">{{ __('admin.leagues.index') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.leagues.create') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.leagues.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <div class="form-group">
                                        <label>{{ __('admin.leagues.name') }} ({{ $properties['native'] }})</label>
                                        <input type="text" name="name[{{ $localeCode }}]" class="form-control" required
                                            value="{{ old('name.' . $localeCode) }}">
                                    </div>
                                @endforeach

                                <div class="form-group">
                                    <label>{{ __('admin.sports.index') }}</label>
                                    <select name="sport_id" class="form-control" required>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.season') }}</label>
                                    <input type="text" name="season" class="form-control" value="{{ old('season') }}">
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.leagues.start_date') }}</label>
                                            <input type="date" name="start_date" class="form-control"
                                                value="{{ old('start_date') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.leagues.end_date') }}</label>
                                            <input type="date" name="end_date" class="form-control"
                                                value="{{ old('end_date') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.leagues.image') }}</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="image" id="leagueImage">
                                        <label class="custom-file-label" for="leagueImage">Choose file</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.sports.active') }}</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1" selected>{{ __('admin.categories.yes') }}</option>
                                        <option value="0">{{ __('admin.categories.no') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions text-right">
                            <a href="{{ route('admin.leagues.index') }}" class="btn btn-warning mr-1">
                                <i class="la la-remove"></i> {{ __('admin.buttons.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-check"></i> {{ __('admin.buttons.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection