@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.community_categories.edit') }}</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form action="{{ route('admin.community_categories.update', $communityCategory->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_en">{{ __('admin.categories.name') }} (EN)</label>
                                            <input type="text" id="name_en" class="form-control round" name="name_en"
                                                value="{{ $communityCategory->name_en }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name_ar">{{ __('admin.categories.name') }} (AR)</label>
                                            <input type="text" id="name_ar" class="form-control round" name="name_ar"
                                                value="{{ $communityCategory->name_ar }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">{{ __('admin.categories.image') }}</label>
                                            <input type="file" id="image" class="form-control-file" name="image">
                                            @if($communityCategory->image)
                                                <div class="mt-2">
                                                    <img src="{{ asset('storage/' . $communityCategory->image) }}"
                                                        alt="Current Image"
                                                        style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions text-right">
                                <a href="{{ route('admin.community_categories.index') }}"
                                    class="btn btn-warning mr-1 rounded-pill">
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