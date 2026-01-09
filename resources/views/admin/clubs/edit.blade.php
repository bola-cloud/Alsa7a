@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.clubs.index') }}">{{ __('Clubs') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Edit Club') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('Edit Club') }}</h3>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Edit Club') }}: {{ $club->name }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.clubs.update', $club->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-info-circle"></i> {{ __('admin.categories.name') }} & {{ __('Description') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.categories.name') }} (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[en]" class="form-control" required value="{{ old('name.en', $club->getTranslation('name', 'en')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.categories.name') }} (AR) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[ar]" class="form-control" required value="{{ old('name.ar', $club->getTranslation('name', 'ar')) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.categories.description') }} (EN)</label>
                                    <textarea name="description[en]" class="form-control" rows="3">{{ old('description.en', $club->getTranslation('description', 'en')) }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.categories.description') }} (AR)</label>
                                    <textarea name="description[ar]" class="form-control" rows="3">{{ old('description.ar', $club->getTranslation('description', 'ar')) }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <hr>
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-map-marker"></i> {{ __('Details') }}</h6>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" required value="{{ old('city', $club->city) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Country</label>
                                    <input type="text" name="country" class="form-control" value="{{ old('country', $club->country) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Website</label>
                                    <input type="url" name="website" class="form-control" value="{{ old('website', $club->website) }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sports</label>
                                    <select name="sports[]" class="form-control select2" multiple>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}" {{ $club->sports->contains($sport->id) ? 'selected' : '' }}>
                                                {{ $sport->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <hr>
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-image"></i> {{ __('Media') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Logo</label>
                                    @if($club->logo_url)
                                        <div class="mb-2">
                                            <img src="{{ $club->logo_url }}" height="60" class="rounded shadow-sm">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                         <input type="file" name="logo" class="custom-file-input" id="logoUpload">
                                         <label class="custom-file-label" for="logoUpload">Choose Logo</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Banner</label>
                                    @if($club->banner_url)
                                        <div class="mb-2">
                                            <img src="{{ $club->banner_url }}" height="60" class="rounded shadow-sm">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" name="banner" class="custom-file-input" id="bannerUpload">
                                        <label class="custom-file-label" for="bannerUpload">Choose Banner</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mt-2">
                                <div class="form-group">
                                    <div class="checklist-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="is_featured" id="is_featured" value="1" {{ $club->is_featured ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold" for="is_featured">Featured Club</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-4 text-right">
                            <a href="{{ route('admin.clubs.index') }}" class="btn btn-warning mr-2">
                                <i class="la la-times"></i> {{ __('admin.buttons.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-check-circle"></i> {{ __('admin.buttons.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
