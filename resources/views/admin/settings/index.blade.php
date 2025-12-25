@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title text-bold-700">{{ __('admin.settings.index') }}</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                @foreach($settings as $group => $groupSettings)
                    <div class="card modern-card mb-4">
                        <div class="card-header">
                            <h4 class="card-title text-capitalize">{{ $group }} Settings</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($groupSettings as $setting)
                                    <div class="col-md-6 mb-3">
                                        <label for="{{ $setting->key }}"
                                            class="form-label font-weight-bold">{{ $setting->label ?? $setting->key }}</label>

                                        @if($setting->type === 'text')
                                            <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control"
                                                value="{{ $setting->value }}">

                                        @elseif($setting->type === 'textarea')
                                            <textarea name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control"
                                                rows="3">{{ $setting->value }}</textarea>

                                        @elseif($setting->type === 'image')
                                            <div class="d-flex align-items-center">
                                                @if($setting->value)
                                                    <div class="mr-3">
                                                        <img src="{{ $setting->image_url }}" alt="{{ $setting->key }}" class="img-thumbnail"
                                                            style="height: 60px;">
                                                    </div>
                                                @endif
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="{{ $setting->key }}"
                                                        id="{{ $setting->key }}">
                                                    <label class="custom-file-label" for="{{ $setting->key }}">Choose file</label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="la la-save"></i> {{ __('admin.buttons.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection