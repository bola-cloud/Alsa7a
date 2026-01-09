@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="border-radius: 20px; box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.sports.edit') }}</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form class="form" action="{{ route('admin.sports.update', $sport->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_en">{{ __('admin.sports.name') }} (EN)</label>
                                            <input type="text" id="name_en" class="form-control round mb-1" name="name[en]"
                                                value="{{ $sport->getTranslation('name', 'en') }}" required>
                                            <label for="name_ar">{{ __('admin.sports.name') }} (AR)</label>
                                            <input type="text" id="name_ar" class="form-control round" name="name[ar]"
                                                value="{{ $sport->getTranslation('name', 'ar') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="icon">{{ __('admin.sports.icon') }}</label>
                                            <input type="file" id="icon" class="form-control-file" name="icon">
                                            @if($sport->icon_url)
                                                <div class="mt-2">
                                                    <img src="{{ $sport->icon_url }}" alt="Icon" width="100" class="rounded">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_en">{{ __('admin.sports.description') }} (EN)</label>
                                            <textarea id="description_en" class="form-control round" name="description[en]"
                                                rows="3">{{ $sport->getTranslation('description', 'en') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_ar">{{ __('admin.sports.description') }} (AR)</label>
                                            <textarea id="description_ar" class="form-control round" name="description[ar]"
                                                rows="3">{{ $sport->getTranslation('description', 'ar') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <fieldset>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" name="active"
                                                        id="active" value="1" {{ $sport->active ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="active">{{ __('admin.sports.active') }}</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions text-right">
                                <a href="{{ route('admin.sports.index') }}" class="btn btn-warning mr-1 rounded-pill">
                                    <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill">
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