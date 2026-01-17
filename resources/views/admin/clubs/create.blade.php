@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.clubs.index') }}">{{ __('Clubs') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Add New Club') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('Add New Club') }}</h3>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('Create Club') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.clubs.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i
                                        class="la la-info-circle"></i> {{ __('admin.categories.name') }} &
                                    {{ __('Description') }}
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.categories.name') }} (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[en]" class="form-control" required
                                        placeholder="Club Name (EN)" value="{{ old('name.en') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.categories.name') }} (AR) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[ar]" class="form-control" required
                                        placeholder="اسم النادي (AR)" value="{{ old('name.ar') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.categories.description') }} (EN)</label>
                                    <textarea name="description[en]" class="form-control" rows="3"
                                        placeholder="Club Description...">{{ old('description.en') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.categories.description') }} (AR)</label>
                                    <textarea name="description[ar]" class="form-control" rows="3"
                                        placeholder="وصف النادي...">{{ old('description.ar') }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <hr>
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-map-marker"></i>
                                    {{ __('Details') }}</h6>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" class="form-control" required
                                        placeholder="e.g. Manchester" value="{{ old('city') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Founded Year</label>
                                    <input type="number" name="founded_year" class="form-control" placeholder="YYYY"
                                        value="{{ old('founded_year') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Website</label>
                                    <input type="url" name="website" class="form-control" placeholder="https://..."
                                        value="{{ old('website') }}">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Sports</label>
                                    <select name="sports[]" class="form-control select2" multiple required>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Leagues</label>
                                    <select name="leagues[]" class="form-control select2" multiple>
                                        @foreach($leagues as $league)
                                            <option value="{{ $league->id }}">{{ $league->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <hr>
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-image"></i>
                                    {{ __('Media') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Logo</label>
                                    <div class="custom-file">
                                        <input type="file" name="logo" class="custom-file-input" id="logoUpload">
                                        <label class="custom-file-label" for="logoUpload">Choose Logo</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Banner</label>
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
                                            <input type="checkbox" class="custom-control-input" name="is_featured"
                                                id="is_featured" value="1">
                                            <label class="custom-control-label font-weight-bold" for="is_featured">Featured
                                                Club</label>
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
                                <i class="la la-check-circle"></i> {{ __('admin.buttons.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection