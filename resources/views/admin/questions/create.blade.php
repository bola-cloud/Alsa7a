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
                                        <option value="text" {{ old('type') == 'text' ? 'selected' : '' }}>
                                            {{ __('admin.questions.types.text') }}</option>
                                        <option value="number" {{ old('type') == 'number' ? 'selected' : '' }}>
                                            {{ __('admin.questions.types.number') }}</option>
                                        <option value="boolean" {{ old('type') == 'boolean' ? 'selected' : '' }}>
                                            {{ __('admin.questions.types.boolean') }}
                                        </option>
                                        <option value="rating" {{ old('type') == 'rating' ? 'selected' : '' }}>
                                            {{ __('admin.questions.types.rating') }}
                                        </option>
                                        <option value="multiple_choice" {{ old('type') == 'multiple_choice' ? 'selected' : '' }}>{{ __('admin.questions.types.multiple_choice') }}</option>
                                    </select>
                                    @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group" id="choicesGroup" style="display: none;">
                                    <label>{{ __('admin.questions.choices') }}</label>
                                    <div id="choices-container">
                                        <!-- Dynamic inputs will appear here -->
                                        <div class="row mb-1 choice-row">
                                            <div class="col-md-5">
                                                <input type="text" name="choice_keys[]" class="form-control"
                                                    placeholder="Key (e.g., red)">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="choice_labels[]" class="form-control"
                                                    placeholder="Label (e.g., Red Color)">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-danger btn-sm remove-choice"><i
                                                        class="la la-trash"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-info btn-sm mt-1" id="add-choice">
                                        <i class="la la-plus"></i> {{ __('admin.buttons.add_new') }}
                                    </button>
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
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const typeSelect = document.getElementById('typeSelect');
                const choicesGroup = document.getElementById('choicesGroup');
                const choicesContainer = document.getElementById('choices-container');
                const addChoiceBtn = document.getElementById('add-choice');

                function toggleChoices() {
                    if (typeSelect.value === 'multiple_choice') {
                        choicesGroup.style.display = 'block';
                    } else {
                        choicesGroup.style.display = 'none';
                    }
                }

                typeSelect.addEventListener('change', toggleChoices);
                toggleChoices(); // Run on load

                // Add Choice
                addChoiceBtn.addEventListener('click', function () {
                    const row = document.createElement('div');
                    row.className = 'row mb-1 choice-row';
                    row.innerHTML = `
                                <div class="col-md-5">
                                     <input type="text" name="choice_keys[]" class="form-control" placeholder="Key">
                                </div>
                                <div class="col-md-5">
                                     <input type="text" name="choice_labels[]" class="form-control" placeholder="Label">
                                </div>
                                <div class="col-md-2">
                                     <button type="button" class="btn btn-danger btn-sm remove-choice"><i class="la la-trash"></i></button>
                                </div>
                            `;
                    choicesContainer.appendChild(row);
                });

                // Remove Choice
                choicesContainer.addEventListener('click', function (e) {
                    if (e.target.closest('.remove-choice')) {
                        e.target.closest('.choice-row').remove();
                    }
                });
            });
        </script>
    @endpush
@endsection