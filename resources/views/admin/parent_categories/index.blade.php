@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-3">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title text-bold-700">{{ __('admin.parent_categories.index') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <a href="{{ route('admin.parent_categories.create') }}" class="btn btn-primary rounded-pill shadow-sm">
                    <i class="la la-plus"></i> {{ __('admin.parent_categories.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($parentCategories as $category)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 20px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.05)';">
                    
                    {{-- Image Area --}}
                    <div class="position-relative" style="height: 180px; overflow: hidden; background: #f8f9fa;">
                        @if($category->image)
                            <img src="{{ $category->image }}" alt="{{ $category->name }}"
                                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-gradient-x-light-blue">
                                <i class="la la-sitemap font-large-4 text-muted opacity-50"></i>
                            </div>
                        @endif
                        <div class="image-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; height: 50%; background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);"></div>
                    </div>

                    <div class="card-body text-center pt-3">
                        <h5 class="font-weight-bold text-dark mb-2" style="font-size: 1.2rem;">{{ $category->name }}</h5>

                        <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 10px;">
                            <a href="{{ route('admin.categories.index', ['parent_category_id' => $category->id]) }}"
                                class="btn btn-icon btn-outline-info rounded-circle" 
                                style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;"
                                title="{{ __('admin.buttons.view') }}">
                                <i class="la la-list"></i>
                            </a>

                            <a href="{{ route('admin.parent_categories.edit', $category->id) }}"
                                class="btn btn-icon btn-outline-primary rounded-circle" 
                                style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;"
                                title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i>
                            </a>

                            @if(!$category->isProtected())
                                <form action="{{ route('admin.parent_categories.destroy', $category->id) }}" method="POST"
                                    onsubmit="return confirm('{{ __('admin.buttons.confirm_delete') }}');" class="mb-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline-danger rounded-circle"
                                        style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;"
                                        title="{{ __('admin.buttons.delete') }}">
                                        <i class="la la-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="la la-sitemap font-large-5 text-muted mb-2"></i>
                <h4 class="text-muted">{{ __('admin.categories.no_records') }}</h4>
                <a href="{{ route('admin.parent_categories.create') }}" class="btn btn-primary mt-2 rounded-pill">
                    {{ __('admin.parent_categories.add_new') }}
                </a>
            </div>
        @endforelse
    </div>
@endsection