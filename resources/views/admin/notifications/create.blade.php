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
                </div>

                <div class="card border mb-3">
                    <div class="card-header py-2">
                        <h5 class="card-title mb-0">{{ __('admin.notifications.target_title') }}</h5>
                        <small class="text-muted">{{ __('admin.notifications.target_hint') }}</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group mb-3">
                                    <label for="target_type" class="form-label">{{ __('admin.notifications.target_type') }}</label>
                                    <select name="target_type" id="target_type" class="form-control @error('target_type') is-invalid @enderror">
                                        @foreach($targets as $target)
                                            <option value="{{ $target['value'] }}" @selected(old('target_type') === $target['value'])>
                                                {{ $target['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('target_type')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-7">
                                <div class="form-group mb-3 d-none" id="target-id-field">
                                    <label for="target_id" class="form-label">{{ __('admin.notifications.target_id') }}</label>
                                    <input type="number" min="1" name="target_id" id="target_id"
                                        class="form-control @error('target_id') is-invalid @enderror"
                                        value="{{ old('target_id') }}"
                                        placeholder="{{ __('admin.notifications.target_id_placeholder') }}">
                                    @error('target_id')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3 d-none" id="target-url-field">
                                    <label for="target_url" class="form-label">{{ __('admin.notifications.target_url') }}</label>
                                    <input type="url" name="target_url" id="target_url"
                                        class="form-control @error('target_url') is-invalid @enderror"
                                        value="{{ old('target_url') }}"
                                        placeholder="https://example.com/page">
                                    @error('target_url')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="text-muted">{{ __('admin.notifications.target_url_hint') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-secondary mb-0 d-none" id="target-preview">
                            <span>{{ __('admin.notifications.target_preview') }}</span>
                            <code id="target-preview-value" class="ms-1" style="direction: ltr; unicode-bidi: embed;"></code>
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
                            <label class="form-label d-block">{{ __('admin.notifications.audience_platform') }}</label>
                            @foreach(['all', 'android', 'ios'] as $platform)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="platform"
                                        value="{{ $platform }}" id="platform_{{ $platform }}"
                                        @checked(old('platform', 'all') === $platform)>
                                    <label class="form-check-label" for="platform_{{ $platform }}">
                                        {{ __('admin.notifications.platform_' . $platform) }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

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

<script>
    (function () {
        var select = document.getElementById('target_type');
        var idField = document.getElementById('target-id-field');
        var urlField = document.getElementById('target-url-field');
        var idInput = document.getElementById('target_id');
        var urlInput = document.getElementById('target_url');
        var preview = document.getElementById('target-preview');
        var previewValue = document.getElementById('target-preview-value');

        // The same table that drives validation on the server, so the form can
        // never offer a field the backend would reject.
        var targets = @json(collect($targets)->keyBy('value'));
        var shareBase = @json(rtrim(config('app.url'), '/') . '/share/');

        function selected() {
            return targets[select.value] || { input: 'none', path: null };
        }

        function render() {
            var target = selected();

            idField.classList.toggle('d-none', target.input !== 'id');
            urlField.classList.toggle('d-none', target.input !== 'url');

            idInput.required = target.input === 'id';
            urlInput.required = target.input === 'url';

            var link = null;
            if (target.input === 'url') {
                link = urlInput.value.trim() || null;
            } else if (target.path !== null && target.path !== undefined) {
                var id = target.input === 'id' ? idInput.value.trim() : '';
                link = shareBase + [target.path, id].filter(Boolean).join('/');
            }

            preview.classList.toggle('d-none', !link);
            previewValue.textContent = link || '';
        }

        select.addEventListener('change', render);
        idInput.addEventListener('input', render);
        urlInput.addEventListener('input', render);

        render();
    })();
</script>
@endpush
