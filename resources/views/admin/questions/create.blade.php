@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.questions.create') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.questions.index') }}">{{ __('admin.menu.questions') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.questions.create') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card admin-card">
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form class="form" action="{{ route('admin.questions.store') }}" method="POST">
                                @csrf

                                <div class="form-body">
                                    <div class="form-group">
                                        <label>{{ __('admin.questions.question') }} (EN)</label>
                                        <input type="text" class="form-control" name="question_en"
                                            value="{{ old('question_en') }}" required>
                                        @error('question_en') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>{{ __('admin.questions.question') }} (AR)</label>
                                        <input type="text" class="form-control" name="question_ar"
                                            value="{{ old('question_ar') }}" required dir="rtl">
                                        @error('question_ar') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>{{ __('admin.questions.category') }}</label>
                                        <select name="category_id" class="form-control" required>
                                            <option value="">{{ __('admin.buttons.select') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>{{ __('admin.questions.type') }}</label>
                                        <select name="type" class="form-control" required id="typeSelect">
                                            <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>Text</option>
                                            <option value="boolean" {{ old('type') == 'boolean' ? 'selected' : '' }}>Yes/No
                                            </option>
                                            <option value="rating" {{ old('type') == 'rating' ? 'selected' : '' }}>Rating
                                            </option>
                                            <option value="multiple_choice" {{ old('type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                        </select>
                                        @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group" id="choicesGroup" style="display: none;">
                                        <label>{{ __('admin.questions.choices') }} (JSON Format: {"option1": "Label 1",
                                            "option2": "Label 2"})</label>
                                        <textarea name="choices" class="form-control"
                                            rows="3">{{ old('choices') }}</textarea>
                                        <p class="text-muted small">Example: {"red": "Red", "blue": "Blue"}</p>
                                        @error('choices') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>

                                </div>

                                <div class="form-actions right">
                                    <a href="{{ route('admin.questions.index') }}" class="btn btn-warning mr-1">
                                        <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="la la-check-square-o"></i> {{ __('admin.buttons.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const typeSelect = document.getElementById('typeSelect');
                const choicesGroup = document.getElementById('choicesGroup');

                function toggleChoices() {
                    if (typeSelect.value === 'multiple_choice') {
                        choicesGroup.style.display = 'block';
                    } else {
                        choicesGroup.style.display = 'none';
                    }
                }

                typeSelect.addEventListener('change', toggleChoices);
                toggleChoices(); // Run on load
            });
        </script>
    @endpush
@endsection