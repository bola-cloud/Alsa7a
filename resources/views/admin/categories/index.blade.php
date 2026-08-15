@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-3">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title text-bold-700">
                {{ __('admin.categories.index') }}
                @if(isset($parentCategory))
                    <small class="text-muted"> / {{ $parentCategory->name }}</small>
                @endif
            </h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <a href="{{ route('admin.categories.create', isset($parentCategory) ? ['parent_category_id' => $parentCategory->id] : []) }}"
                    class="btn btn-primary rounded-pill shadow-sm">
                    <i class="la la-plus"></i> {{ __('admin.categories.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($categories as $category)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 20px; transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 15px 30px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.05)';">
                    
                    {{-- Provider Badge --}}
                    @if($category->is_service_provider)
                        <span class="badge badge-info position-absolute" style="top: 15px; right: 15px; z-index: 10; padding: 8px 15px; border-radius: 30px; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                            <i class="la la-briefcase"></i> {{ __('admin.categories.provider') }}
                        </span>
                    @endif

                    {{-- Image Area --}}
                    <div class="position-relative" style="height: 180px; overflow: hidden; background: #f8f9fa;">
                        @if($category->image)
                            <img src="{{ $category->image }}" alt="{{ $category->name }}"
                                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;">
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 w-100 bg-gradient-x-light-blue">
                                <i class="la la-cube font-large-4 text-muted opacity-50"></i>
                            </div>
                        @endif
                        <div class="image-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; height: 50%; background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);"></div>
                    </div>

                    <div class="card-body text-center pt-3">
                        <h5 class="font-weight-bold text-dark mb-1" style="font-size: 1.2rem;">{{ $category->name }}</h5>
                        <p class="text-muted small px-2">
                            {{ Str::limit($category->description, 60) ?? __('admin.messages.no_description') }}
                        </p>

                        <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 8px;">
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-icon btn-outline-primary rounded-circle"
                                style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;"
                                title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i>
                            </a>

                            <a href="{{ route('admin.questions.index', ['category_id' => $category->id]) }}"
                                class="btn btn-icon btn-outline-info rounded-circle" 
                                style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;"
                                title="{{ __('admin.menu.questions') }}">
                                <i class="la la-question-circle"></i>
                            </a>

                            <a href="{{ route('admin.categories.verification', $category->id) }}"
                                class="btn btn-icon btn-outline-warning rounded-circle" 
                                style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;"
                                title="{{ __('admin.categories.verification_settings') }}">
                                <i class="la la-shield"></i>
                            </a>

                            @if(!$category->isProtected())
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                    onsubmit="return confirm('{{ __('admin.buttons.confirm_delete') }}');" class="mb-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-outline-danger rounded-circle"
                                        style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;"
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
                <i class="la la-folder-open font-large-5 text-muted mb-2"></i>
                <h4 class="text-muted">{{ __('admin.categories.no_records') }}</h4>
                <a href="{{ route('admin.categories.create', isset($parentCategory) ? ['parent_category_id' => $parentCategory->id] : []) }}"
                    class="btn btn-primary mt-2 rounded-pill">
                    {{ __('admin.buttons.add_new') }}
                </a>
            </div>
        @endforelse
    </div>

    <div class="row mt-3">
        <div class="col-12 d-flex justify-content-center">
            {{ $categories->links() }}
        </div>
    </div>
@endsection