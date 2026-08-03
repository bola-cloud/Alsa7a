@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('admin.countries.create') }}</h4>
            </div>
            <div class="card-content collapse show">
                <div class="card-body">
                    <form action="{{ route('admin.countries.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_en">{{ __('admin.countries.name_en') }}</label>
                                        <input type="text" id="name_en" class="form-control round" name="name_en" value="{{ old('name_en') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name_ar">{{ __('admin.countries.name_ar') }}</label>
                                        <input type="text" id="name_ar" class="form-control round" name="name_ar" value="{{ old('name_ar') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="code">{{ __('admin.countries.code') }}</label>
                                        <input type="text" id="code" class="form-control round" name="code" value="{{ old('code') }}" required placeholder="e.g. EG, SA, AE">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="flag">{{ __('admin.countries.flag') }}</label>
                                        <input type="file" id="flag" class="form-control-file" name="flag">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <fieldset>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="is_active" id="is_active" value="1" checked>
                                                <label class="custom-control-label" for="is_active">{{ __('admin.countries.is_active') }}</label>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions text-right">
                            <a href="{{ route('admin.countries.index') }}" class="btn btn-warning mr-1 rounded-pill">
                                <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill">
                                <i class="la la-check-square-o"></i> {{ __('admin.buttons.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
