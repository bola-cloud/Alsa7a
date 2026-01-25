@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title text-bold-700">{{ __('admin.parent_categories.index') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <a href="{{ route('admin.parent_categories.create') }}" class="btn btn-primary">
                    <i class="la la-plus"></i> {{ __('admin.parent_categories.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($parentCategories as $category)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="modern-card h-100">
                    {{-- Image Area --}}
                    <div class="card-img-top-wrapper"
                        style="height: 140px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);">
                        @if($category->image)
                            <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                style="height: 80px; width: 80px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 w-100">
                                <i class="la la-cube font-large-3 text-muted"></i>
                            </div>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">{{ $category->name }}</h5>

                        <div class="card-actions">
                            <a href="{{ route('admin.parent_categories.edit', $category->id) }}"
                                class="btn btn-sm btn-outline-primary" title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i>
                            </a>

                            <form action="{{ route('admin.parent_categories.destroy', $category->id) }}" method="POST"
                                onsubmit="return confirm('{{ __('admin.buttons.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                    title="{{ __('admin.buttons.delete') }}">
                                    <i class="la la-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-muted">{{ __('admin.categories.no_records') }}</h4>
                        <a href="{{ route('admin.parent_categories.create') }}" class="btn btn-primary mt-2">
                            {{ __('admin.parent_categories.add_new') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection