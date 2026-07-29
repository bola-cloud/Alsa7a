@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('admin.notifications.create') }}</h3>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.notifications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group mb-3">
                    <label for="title" class="form-label">{{ __('admin.notifications.form_title') }}</label>
                    <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title') }}" required>
                    @error('title')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="message" class="form-label">{{ __('admin.notifications.form_message') }}</label>
                    <textarea name="message" id="message" rows="4"
                        class="form-control @error('message') is-invalid @enderror" required>{{ old('message') }}</textarea>
                    @error('message')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">{{ __('admin.notifications.form_image') }}</label>
                            <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="meta_key" class="form-label">{{ __('admin.notifications.form_meta_key') }}</label>
                            <select name="meta_key" id="meta_key" class="form-control">
                                <option value="">{{ __('admin.notifications.meta_none') }}</option>
                                <option value="url">{{ __('admin.notifications.meta_url') }}</option>
                                <option value="post_id">{{ __('admin.notifications.meta_post') }}</option>
                                <option value="user_id">{{ __('admin.notifications.meta_user') }}</option>
                                <option value="custom">{{ __('admin.notifications.meta_custom') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="meta_value" class="form-label">{{ __('admin.notifications.form_meta_value') }}</label>
                            <input type="text" name="meta_value" id="meta_value" class="form-control" placeholder="{{ __('admin.notifications.meta_value_placeholder') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> {{ __('admin.notifications.send_btn') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection