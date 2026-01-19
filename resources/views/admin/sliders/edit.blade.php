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
                                href="{{ route('admin.sliders.index') }}">{{ __('admin.sliders.index') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.sliders.edit') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.sliders.edit') }}</h3>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.sliders.edit') }}: {{ $slider->title }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sliders.update', $slider->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i
                                        class="la la-info-circle"></i> {{ __('admin.categories.name') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sliders.title') }} (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="title[en]" class="form-control" required
                                        value="{{ old('title.en', $slider->getTranslation('title', 'en')) }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sliders.title') }} (AR) <span class="text-danger">*</span></label>
                                    <input type="text" name="title[ar]" class="form-control" required
                                        value="{{ old('title.ar', $slider->getTranslation('title', 'ar')) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sliders.description') }} (EN)</label>
                                    <textarea name="description[en]" class="form-control"
                                        rows="3">{{ old('description.en', $slider->getTranslation('description', 'en') ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sliders.description') }} (AR)</label>
                                    <textarea name="description[ar]" class="form-control"
                                        rows="3">{{ old('description.ar', $slider->getTranslation('description', 'ar') ?? '') }}</textarea>
                                </div>
                            </div>

                            <div class="col-12 mt-4 mb-3">
                                <hr>
                                <h6 class="text-muted text-uppercase font-weight-bold mb-3"><i class="la la-image"></i>
                                    {{ __('Media') }}</h6>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sliders.image') }}</label>

                                    <div class="mb-2">
                                        @if($slider->image)
                                            <img id="imagePreview" src="{{ $slider->image_url }}" alt="Slider"
                                                class="img-fluid rounded shadow-sm" style="max-height: 150px;">
                                        @else
                                            <img id="imagePreview" src="#" alt="Image Preview"
                                                class="img-fluid rounded shadow-sm" style="max-height: 150px; display: none;">
                                        @endif
                                    </div>

                                    <div class="custom-file">
                                        <input type="file" name="image" class="custom-file-input" id="imageUpload">
                                        <label class="custom-file-label" for="imageUpload">Choose Image</label>
                                    </div>
                                    <small class="text-muted">Recommended Size: 1920x600 px</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions mt-4 text-right">
                            <a href="{{ route('admin.sliders.index') }}" class="btn btn-warning mr-2">
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

    @push('scripts')
        <script>
            $(document).ready(function () {
                $('#imageUpload').change(function (event) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#imagePreview').attr('src', e.target.result).show();
                    }
                    if (event.target.files && event.target.files[0]) {
                        reader.readAsDataURL(event.target.files[0]);
                        $(this).next('.custom-file-label').html(event.target.files[0].name);
                    }
                });
            });
        </script>
    @endpush
@endsection