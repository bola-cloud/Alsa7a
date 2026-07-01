@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.categories.verification_settings') }} - {{ $category->name }}</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form action="{{ route('admin.categories.update_verification', $category->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <fieldset>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="requires_verification" id="requires_verification" value="1"
                                                        {{ $category->requires_verification ? 'checked' : '' }}
                                                        onchange="toggleVerificationRequirements(this)">
                                                    <label class="custom-control-label"
                                                        for="requires_verification">{{ __('admin.categories.requires_verification') }}</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <fieldset>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="mandatory_service_verification" id="mandatory_service_verification" value="1"
                                                        {{ $category->mandatory_service_verification ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="mandatory_service_verification">{{ __('admin.categories.mandatory_service_verification') }}</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>

                                <div id="verification_requirements_container" style="{{ $category->requires_verification ? '' : 'display: none;' }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="verification_requirements_en">{{ __('admin.categories.verification_requirements') }} (EN)</label>
                                                <textarea id="verification_requirements_en" class="form-control round" name="verification_requirements[en]"
                                                    rows="3">{{ $category->verification_requirements_en }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="verification_requirements_ar">{{ __('admin.categories.verification_requirements') }} (AR)</label>
                                                <textarea id="verification_requirements_ar" class="form-control round" name="verification_requirements[ar]"
                                                    rows="3">{{ $category->verification_requirements_ar }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <h5>{{ __('admin.categories.verification_fields') }}</h5>
                                            <div id="dynamic_fields_container">
                                                @if($category->verification_fields)
                                                    @foreach($category->verification_fields as $index => $field)
                                                        <div class="row field-row mb-1">
                                                            <div class="col-md-3">
                                                                <input type="text" name="verification_fields[{{$index}}][id]" class="form-control" placeholder="{{ __('admin.categories.field_id') }}" value="{{ $field['id'] ?? '' }}">
                                                            </div>
                                                            <div class="col-md-2">
                                                                <select name="verification_fields[{{$index}}][type]" class="form-control">
                                                                    <option value="file" {{ ($field['type'] ?? '') == 'file' ? 'selected' : '' }}>File</option>
                                                                    <option value="text" {{ ($field['type'] ?? '') == 'text' ? 'selected' : '' }}>Text</option>
                                                                    <option value="number" {{ ($field['type'] ?? '') == 'number' ? 'selected' : '' }}>Number</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="text" name="verification_fields[{{$index}}][label_en]" class="form-control" placeholder="{{ __('admin.categories.field_label') }} (EN)" value="{{ $field['label_en'] ?? '' }}">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="text" name="verification_fields[{{$index}}][label_ar]" class="form-control" placeholder="{{ __('admin.categories.field_label') }} (AR)" value="{{ $field['label_ar'] ?? '' }}">
                                                            </div>
                                                            <div class="col-md-1">
                                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeField(this)"><i class="la la-trash"></i></button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                            <button type="button" class="btn btn-info btn-sm mt-1" onclick="addField()"><i class="la la-plus"></i> {{ __('admin.categories.add_field') }}</button>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    let fieldIndex = {{ $category->verification_fields ? count($category->verification_fields) : 0 }};
                                    function addField() {
                                        const container = document.getElementById('dynamic_fields_container');
                                        const row = document.createElement('div');
                                        row.className = 'row field-row mb-1';
                                        row.innerHTML = `
                                            <div class="col-md-3">
                                                <input type="text" name="verification_fields[${fieldIndex}][id]" class="form-control" placeholder="{{ __('admin.categories.field_id') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <select name="verification_fields[${fieldIndex}][type]" class="form-control">
                                                    <option value="file">File</option>
                                                    <option value="text">Text</option>
                                                    <option value="number">Number</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="verification_fields[${fieldIndex}][label_en]" class="form-control" placeholder="{{ __('admin.categories.field_label') }} (EN)">
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" name="verification_fields[${fieldIndex}][label_ar]" class="form-control" placeholder="{{ __('admin.categories.field_label') }} (AR)">
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-danger btn-sm" onclick="removeField(this)"><i class="la la-trash"></i></button>
                                            </div>
                                        `;
                                        container.appendChild(row);
                                        fieldIndex++;
                                    }
                                    function removeField(btn) {
                                        btn.closest('.field-row').remove();
                                    }
                                    function toggleVerificationRequirements(checkbox) {
                                        document.getElementById('verification_requirements_container').style.display = checkbox.checked ? 'block' : 'none';
                                    }
                                </script>
                            </div>

                            <div class="form-actions text-right mt-2">
                                <a href="{{ route('admin.categories.index', ['parent_category_id' => $category->parent_category_id]) }}" class="btn btn-warning mr-1 rounded-pill">
                                    <i class="la la-close"></i> {{ __('admin.buttons.cancel') }}
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
