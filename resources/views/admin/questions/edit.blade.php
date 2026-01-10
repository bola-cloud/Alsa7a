@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.questions.edit') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.questions.index') }}">{{ __('admin.menu.questions') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.questions.edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card admin-card">
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form class="form" action="{{ route('admin.questions.update', $question->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-body">
                                <div class="form-group">
                                    <label>{{ __('admin.questions.question') }} (EN)</label>
                                    {{-- Assuming 'question' is translatable or stored as JSON, but treating as separate
                                    fields for safety if Translatble trait usage --}}
                                    {{-- If using Spatie Translatable, we access translation directly --}}
                                    <input type="text" class="form-control" name="question_en"
                                        value="{{ old('question_en', $question->getTranslation('question', 'en')) }}"
                                        required>
                                    @error('question_en') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.questions.question') }} (AR)</label>
                                    <input type="text" class="form-control" name="question_ar"
                                        value="{{ old('question_ar', $question->getTranslation('question', 'ar')) }}"
                                        required dir="rtl">
                                    @error('question_ar') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.questions.category') }}</label>
                                    <select name="category_id" class="form-control" required>
                                        <option value="">{{ __('admin.buttons.select') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $question->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.questions.type') }}</label>
                                    <select name="type" class="form-control" required id="typeSelect">
                                        <option value="text" {{ old('type', $question->type) == 'text' ? 'selected' : '' }}>{{ __('admin.questions.types.text') }}</option>
                                        <option value="boolean" {{ old('type', $question->type) == 'boolean' ? 'selected' : '' }}>{{ __('admin.questions.types.boolean') }}</option>
                                        <option value="rating" {{ old('type', $question->type) == 'rating' ? 'selected' : '' }}>{{ __('admin.questions.types.rating') }}</option>
                                        <option value="multiple_choice" {{ old('type', $question->type) == 'multiple_choice' ? 'selected' : '' }}>{{ __('admin.questions.types.multiple_choice') }}
                                        </option>
                                    </select>
                                    @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group" id="choicesGroup" style="display: none;">
                                    <label>{{ __('admin.questions.choices') }} (JSON Format)</label>
                                    <textarea name="choices" class="form-control"
                                        rows="3">{{ old('choices', is_array($question->choices) ? json_encode($question->choices) : $question->choices) }}</textarea>
                                    <p class="text-muted small">Example: {"red": "Red", "blue": "Blue"}</p>
                                    @error('choices') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                            </div>

                            <div class="form-actions right">
                                <a href="{{ route('admin.questions.index') }}" class="btn btn-warning mr-1">
                                    <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="la la-check-square-o"></i> {{ __('admin.buttons.update') }}
                                </button>
                            </div>
                        </form>
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