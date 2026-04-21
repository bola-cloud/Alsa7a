@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">Questions</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">Questions</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right" role="group" aria-label="Button group with nested dropdown">
                <a href="{{ route('admin.questions.create') }}" class="btn btn-info round box-shadow-2 px-2" type="button">
                    <i class="ft-plus icon-left"></i> {{ __('admin.buttons.add_new') }}
                </a>
            </div>
            @if(request('category_id'))
                <div class="float-md-right mr-2">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary round box-shadow-2 px-2">
                        <i class="ft-arrow-left icon-left"></i> {{ __('admin.buttons.back') }}
                    </a>
                </div>
            @endif
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <div class="card admin-card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.buttons.filter') }}</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form action="{{ route('admin.questions.index') }}" method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('admin.buttons.search') }}</label>
                                        <input type="text" name="search" class="form-control"
                                            value="{{ request('search') }}"
                                            placeholder="{{ __('admin.buttons.search') }}...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('admin.menu.categories') }}</label>
                                        <select name="category_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">{{ __('admin.buttons.all') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group" style="margin-top: 25px;">
                                        <button type="submit"
                                            class="btn btn-primary btn-block">{{ __('admin.buttons.filter') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card admin-card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>{{ __('admin.questions.question') }}</th>
                                        <th>{{ __('admin.questions.type') }}</th>
                                        <th>{{ __('admin.questions.category') }}</th>
                                        <th>{{ __('admin.questions.sort_order') }}</th>
                                        <th style="width: 50px;"></th>
                                        <th>{{ __('admin.buttons.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-questions">
                                    @forelse($questions as $question)
                                        <tr data-id="{{ $question->id }}">
                                            <td>{{ $question->id }}</td>
                                            <td>{{ $question->getTranslation('question', app()->getLocale()) }}</td>
                                            <td><span
                                                    class="badge badge-info">{{ __('admin.questions.types.' . $question->type) }}</span>
                                            </td>
                                            <td>
                                                @if($question->category)
                                                    <span class="badge badge-primary">{{ $question->category->name }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="sort-order-value">{{ $question->sort_order }}</td>
                                            <td>
                                                @if(request()->filled('category_id') && request()->category_id != 'all')
                                                    <i class="la la-arrows-v drag-handle" style="cursor: move; font-size: 20px;"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.questions.answers', $question->id) }}"
                                                    class="btn btn-sm btn-info" title="View Answers"><i
                                                        class="la la-users"></i></a>
                                                <a href="{{ route('admin.questions.edit', $question->id) }}"
                                                    class="btn btn-sm btn-primary"><i class="la la-edit"></i></a>
                                                <form action="{{ route('admin.questions.destroy', $question->id) }}"
                                                    method="POST" style="display:inline-block;"
                                                    onsubmit="return confirm('{{ __('admin.messages.confirm_delete') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"><i
                                                            class="la la-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">
                                                {{ __('admin.categories.no_records') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            {{ $questions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('css')
        <style>
            .drag-handle {
                color: #ccc;
                transition: color 0.2s;
            }
            .drag-handle:hover {
                color: #333;
            }
            .sortable-ghost {
                opacity: 0.4;
                background-color: #f4f5fa !important;
            }
        </style>
    @endpush
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('sortable-questions');
            const isFiltered = "{{ request()->filled('category_id') && request()->category_id != 'all' }}";
            
            if (el && isFiltered) {
                Sortable.create(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function() {
                        let order = [];
                        document.querySelectorAll('#sortable-questions tr').forEach((tr, index) => {
                            order.push({
                                id: tr.getAttribute('data-id'),
                                sort_order: index + 1
                            });
                        });

                        // Send AJAX to update order
                        fetch("{{ route('admin.questions.reorder') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: order })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status) {
                                // Update visual order numbers if needed
                                document.querySelectorAll('#sortable-questions tr').forEach((tr, index) => {
                                    tr.querySelector('.sort-order-value').innerText = index + 1;
                                });
                                toastr.success('{{ __("admin.messages.updated_successfully") }}');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            toastr.error('Something went wrong');
                        });
                    }
                });
            }
        });
    </script>
@endpush