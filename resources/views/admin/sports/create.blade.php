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
                                href="{{ route('admin.sports.index') }}">{{ __('admin.sports.index') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.sports.create') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.sports.create') }}</h3>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.sports.create') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sports.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i
                                        class="la la-info-circle"></i> {{ __('admin.categories.name') }} &
                                    {{ __('Description') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sports.name') }} (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[en]" class="form-control" required
                                        placeholder="Sport Name (EN)" value="{{ old('name.en') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sports.name') }} (AR) <span class="text-danger">*</span></label>
                                    <input type="text" name="name[ar]" class="form-control" required
                                        placeholder="اسم الرياضة (AR)" value="{{ old('name.ar') }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sports.description') }} (EN)</label>
                                    <textarea name="description[en]" class="form-control" rows="3"
                                        placeholder="Description...">{{ old('description.en') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sports.description') }} (AR)</label>
                                    <textarea name="description[ar]" class="form-control" rows="3"
                                        placeholder="الوصف...">{{ old('description.ar') }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <hr>
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-image"></i>
                                    {{ __('Media') }} & {{ __('Settings') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sports.icon') }} <span class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" name="icon" class="custom-file-input" id="iconUpload" required>
                                        <label class="custom-file-label" for="iconUpload">Choose Icon</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mt-4">
                                    <div class="checklist-item">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="active" id="active"
                                                value="1" checked>
                                            <label class="custom-control-label font-weight-bold"
                                                for="active">{{ __('admin.sports.active') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-4 text-right">
                            <a href="{{ route('admin.sports.index') }}" class="btn btn-warning mr-2">
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