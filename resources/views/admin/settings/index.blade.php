@extends('layouts.admin')

@push('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <style>
        .note-editor.note-frame {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            overflow: hidden;
        }
        .note-toolbar {
            background-color: #f9fafb !important;
            border-bottom: 1px solid #d1d5db !important;
        }
    </style>
@endpush

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
                            <h4 class="card-title text-capitalize">{{ __('admin.settings.' . $group) }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($groupSettings as $setting)
                                    <div class="{{ $setting->type === 'richtext' ? 'col-md-12' : 'col-md-6' }} mb-3">
                                        <label for="{{ $setting->key }}"
                                            class="form-label font-weight-bold">{{ __($setting->label) ?? $setting->key }}</label>

                                        @if($setting->type === 'text')
                                            <input type="text" name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control"
                                                value="{{ $setting->value }}">

                                        @elseif($setting->type === 'textarea')
                                            <textarea name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control"
                                                rows="3">{{ $setting->value }}</textarea>

                                        @elseif($setting->type === 'richtext')
                                            <textarea name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control richtext"
                                                rows="10">{{ $setting->value }}</textarea>

                                        @elseif($setting->type === 'select')
                                            <select name="{{ $setting->key }}" id="{{ $setting->key }}" class="form-control">
                                                @foreach($setting->options as $value => $label)
                                                    <option value="{{ $value }}" {{ $setting->value == $value ? 'selected' : '' }}>
                                                        {{ __($label) }}
                                                    </option>
                                                @endforeach
                                            </select>

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
                                                    <label class="custom-file-label"
                                                        for="{{ $setting->key }}">{{ __('admin.settings.choose_file') }}</label>
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
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.richtext').summernote({
                height: 400,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onInit: function() {
                        $(this).summernote('code', $(this).val());
                    }
                },
                lang: '{{ app()->getLocale() == "ar" ? "ar-AR" : "en-US" }}'
            });
        });
    </script>
@endpush
@endsection