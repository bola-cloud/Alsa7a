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

                <div class="card border mb-3">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0">{{ __('admin.notifications.audience_title') }}</h5>
                        <small class="text-muted">{{ __('admin.notifications.audience_hint') }}</small>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="country_id" class="form-label">{{ __('admin.notifications.audience_country') }}</label>
                            <select name="country_id" id="country_id" class="form-control">
                                <option value="all">{{ __('admin.notifications.audience_all_countries') }}</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->id }}" @selected(old('country_id') == $country->id)>
                                        {{ app()->getLocale() === 'ar' ? $country->name_ar : $country->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-label d-block">
                                {{ __('admin.notifications.audience_categories') }}
                                <small class="text-muted">— {{ __('admin.notifications.audience_all_categories') }}</small>
                            </label>
                            <div class="row">
                                @foreach($categories as $category)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input audience-category" type="checkbox"
                                                name="category_ids[]" value="{{ $category->id }}"
                                                id="category_{{ $category->id }}"
                                                @checked(in_array($category->id, (array) old('category_ids', [])))>
                                            <label class="form-check-label" for="category_{{ $category->id }}">
                                                {{ $category->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0" id="audience-preview">
                            {{ __('admin.notifications.audience_loading') }}
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

@push('js')
<script>
    (function () {
        var box = document.getElementById('audience-preview');
        var country = document.getElementById('country_id');
        var categories = document.querySelectorAll('.audience-category');
        var url = @json(route('admin.notifications.audience'));
        var template = @json(__('admin.notifications.audience_summary'));
        var pending = null;

        function refresh() {
            var params = new URLSearchParams();
            params.append('country_id', country.value);
            categories.forEach(function (input) {
                if (input.checked) {
                    params.append('category_ids[]', input.value);
                }
            });

            if (pending) {
                pending.abort();
            }
            pending = new AbortController();

            fetch(url + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: pending.signal,
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    box.textContent = template
                        .replace(':total', data.total)
                        .replace(':reachable', data.reachable);
                })
                .catch(function (error) {
                    if (error.name !== 'AbortError') {
                        box.textContent = '—';
                    }
                });
        }

        country.addEventListener('change', refresh);
        categories.forEach(function (input) {
            input.addEventListener('change', refresh);
        });

        refresh();
    })();
</script>
@endpush
