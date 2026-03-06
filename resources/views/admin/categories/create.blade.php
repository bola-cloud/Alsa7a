@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.categories.create') }}</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_en">{{ __('admin.categories.name') }} (EN)</label>
                                            <input type="text" id="name_en" class="form-control round" name="name[en]"
                                                value="{{ old('name.en') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_ar">{{ __('admin.categories.name') }} (AR)</label>
                                            <input type="text" id="name_ar" class="form-control round" name="name[ar]"
                                                value="{{ old('name.ar') }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label
                                                for="parent_category_id">{{ __('admin.parent_categories.index') }}</label>
                                            <select name="parent_category_id" id="parent_category_id" class="form-control"
                                                required>
                                                <option value="">{{ __('admin.buttons.select') }}</option>
                                                @foreach($parentCategories as $parent)
                                                    <option value="{{ $parent->id }}" {{ (old('parent_category_id') == $parent->id || (isset($selectedParentId) && $selectedParentId == $parent->id)) ? 'selected' : '' }}>
                                                        {{ $parent->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">{{ __('admin.categories.image') }}</label>
                                            <input type="file" id="image" class="form-control-file" name="image">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_en">{{ __('admin.categories.description') }}
                                                (EN)</label>
                                            <textarea id="description_en" class="form-control round" name="description[en]"
                                                rows="3">{{ old('description.en') }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_ar">{{ __('admin.categories.description') }}
                                                (AR)</label>
                                            <textarea id="description_ar" class="form-control round" name="description[ar]"
                                                rows="3">{{ old('description.ar') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <fieldset>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="is_service_provider" id="is_service_provider" value="1">
                                                    <label class="custom-control-label"
                                                        for="is_service_provider">{{ __('admin.categories.is_service_provider') }}</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <fieldset>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="requires_verification" id="requires_verification" value="1"
                                                        onchange="toggleVerificationRequirements(this)">
                                                    <label class="custom-control-label"
                                                        for="requires_verification">{{ __('admin.categories.requires_verification') }}</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>

                                <div id="verification_requirements_container" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="verification_requirements_en">{{ __('admin.categories.verification_requirements') }}
                                                    (EN)</label>
                                                <textarea id="verification_requirements_en" class="form-control round"
                                                    name="verification_requirements[en]"
                                                    rows="3">{{ old('verification_requirements.en') }}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label
                                                    for="verification_requirements_ar">{{ __('admin.categories.verification_requirements') }}
                                                    (AR)</label>
                                                <textarea id="verification_requirements_ar" class="form-control round"
                                                    name="verification_requirements[ar]"
                                                    rows="3">{{ old('verification_requirements.ar') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            <h5>{{ __('admin.categories.verification_fields') }}
                                            </h5>
                                            <div id="dynamic_fields_container">
                                            </div>
                                            <button type="button" class="btn btn-info btn-sm mt-1" onclick="addField()"><i
                                                    class="ft-plus"></i> {{ __('admin.categories.add_field') }}</button>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    let fieldIndex = 0;
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
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeField(this)"><i class="ft-trash"></i></button>
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
                                    document.addEventListener('DOMContentLoaded', function () {
                                        toggleVerificationRequirements(document.getElementById('requires_verification'));
                                    });
                                </script>
                            </div>

                            <div class="form-actions text-right">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-warning mr-1 rounded-pill">
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