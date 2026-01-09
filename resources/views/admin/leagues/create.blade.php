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
                                href="{{ route('admin.leagues.index') }}">{{ __('admin.leagues.index') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.leagues.create') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.leagues.create') }}</h3>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.leagues.create') }} - {{ __('admin.leagues.index') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.leagues.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- Basic Information Section -->
                            <div class="col-12 mb-3">
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i
                                        class="la la-info-circle"></i> {{ __('admin.leagues.name') }} &
                                    {{ __('admin.leagues.description') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.name') }} (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[en]" class="form-control" required
                                        placeholder="Ex: Premier League" value="{{ old('name.en') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.name') }} (AR) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[ar]" class="form-control" required
                                        placeholder="مثال: الدوري الممتاز" value="{{ old('name.ar') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.description') }} (EN)</label>
                                    <textarea name="description[en]" class="form-control" rows="3"
                                        placeholder="Enter description in English...">{{ old('description.en') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.description') }} (AR)</label>
                                    <textarea name="description[ar]" class="form-control" rows="3"
                                        placeholder="أدخل الوصف بالعربية...">{{ old('description.ar') }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <hr>
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-calendar"></i>
                                    {{ __('admin.leagues.season') }} & {{ __('admin.leagues.active') }}</h6>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('admin.sports.index') }} <span class="text-danger">*</span></label>
                                    <select name="sport_id" class="form-control select2" required>
                                        <option value="">{{ __('admin.settings.choose_file') }}...</option>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.season') }}</label>
                                    <input type="text" name="season" class="form-control" value="{{ old('season') }}"
                                        placeholder="2025/2026">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('admin.sports.active') }}</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1" selected>{{ __('admin.categories.yes') }}</option>
                                        <option value="0">{{ __('admin.categories.no') }}</option>
                                    </select>
                                </div>
                            </div>

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
                                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                </div>
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <hr>
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-image"></i>
                                    {{ __('admin.leagues.image') }}</h6>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{ __('admin.leagues.image') }}</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="image" id="leagueImage">
                                        <label class="custom-file-label"
                                            for="leagueImage">{{ __('admin.settings.choose_file') }}</label>
                                    </div>
                                    <small class="text-muted">Recommended size: 500x500px (JPG, PNG)</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-4 text-right">
                            <a href="{{ route('admin.leagues.index') }}" class="btn btn-warning mr-2">
                                <i class="la la-times"></i> {{ __('admin.buttons.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-check-circle"></i> {{ __('admin.buttons.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection