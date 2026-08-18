@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.categories.edit') }}</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        @if($category->isProtected())
                            <div class="alert alert-warning d-flex align-items-center" role="alert">
                                <i class="la la-lock mr-2 font-medium-2"></i>
                                <div>
                                    هذا القسم أساسي في النظام — الاسم والقسم الرئيسي غير قابلين للتعديل ولا يمكن حذفه.
                                    باقي الحقول (الصورة، الوصف، والخيارات) متاحة للتعديل بحرية.
                                </div>
                            </div>
                        @endif
                        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_en">{{ __('admin.categories.name') }} (EN)</label>
                                            <input type="text" id="name_en" class="form-control round" name="name[en]"
                                                value="{{ $category->name_en }}" {{ $category->isProtected() ? 'disabled' : 'required' }}>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_ar">{{ __('admin.categories.name') }} (AR)</label>
                                            <input type="text" id="name_ar" class="form-control round" name="name[ar]"
                                                value="{{ $category->name_ar }}" {{ $category->isProtected() ? 'disabled' : 'required' }}>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-light border mb-2 py-2">
                                            <small class="text-muted">{{ __('admin.categories.display_name_hint') }}</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="display_name_en">{{ __('admin.categories.display_name') }} (EN)</label>
                                            <input type="text" id="display_name_en" class="form-control round"
                                                name="display_name[en]" value="{{ $category->display_name_en }}"
                                                placeholder="{{ $category->name_en }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="display_name_ar">{{ __('admin.categories.display_name') }} (AR)</label>
                                            <input type="text" id="display_name_ar" class="form-control round"
                                                name="display_name[ar]" value="{{ $category->display_name_ar }}"
                                                placeholder="{{ $category->name_ar }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label
                                                for="parent_category_id">{{ __('admin.parent_categories.index') }}</label>
                                            <select name="parent_category_id" id="parent_category_id" class="form-control"
                                                {{ $category->isProtected() ? 'disabled' : 'required' }}>
                                                <option value="">{{ __('admin.buttons.select') }}</option>
                                                @foreach($parentCategories as $parent)
                                                    <option value="{{ $parent->id }}" {{ $category->parent_category_id == $parent->id ? 'selected' : '' }}>
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
                                            @if($category->image)
                                                <div class="mt-2">
                                                    <img src="{{ $category->image }}" alt="Current Image"
                                                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_en">{{ __('admin.categories.description') }}
                                                (EN)</label>
                                            <textarea id="description_en" class="form-control round" name="description[en]"
                                                rows="3">{{ $category->description_en }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description_ar">{{ __('admin.categories.description') }}
                                                (AR)</label>
                                            <textarea id="description_ar" class="form-control round" name="description[ar]"
                                                rows="3">{{ $category->description_ar }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <fieldset>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="is_service_provider" id="is_service_provider" value="1" {{ $category->is_service_provider ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="is_service_provider">{{ __('admin.categories.is_service_provider') }}</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <fieldset>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="is_marketplace" id="is_marketplace" value="1" {{ $category->is_marketplace ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="is_marketplace">سوق التعاقدات (Marketplace)</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="alert alert-light border d-flex align-items-center justify-content-between">
                                            <div>
                                                <strong>{{ __('admin.categories.verification_settings') }}</strong>
                                                <div class="text-muted">{{ __('admin.categories.verification_moved_hint') }}</div>
                                            </div>
                                            <a href="{{ route('admin.categories.verification', $category->id) }}"
                                                class="btn btn-info rounded-pill">
                                                <i class="la la-shield"></i>
                                                {{ __('admin.categories.manage_verification') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions text-right">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-warning mr-1 rounded-pill">
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