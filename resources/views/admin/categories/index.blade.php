@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title text-bold-700">
                {{ __('admin.categories.index') }} (V2)
                @if(isset($parentCategory))
                    <small class="text-muted"> / {{ $parentCategory->name }}</small>
                @endif
            </h3>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <a href="{{ route('admin.categories.create', isset($parentCategory) ? ['parent_category_id' => $parentCategory->id] : []) }}"
                    class="btn btn-primary">
                    <i class="la la-plus"></i> {{ __('admin.categories.add_new') }}
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($categories as $category)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-4">
                <div class="modern-card h-100">
                    {{-- Provider Badge --}}
                    @if($category->is_service_provider)
                        <span class="status-badge bg-info">{{ __('admin.categories.provider') }}</span>
                    @endif

                    {{-- Image Area --}}
                    <div class="card-img-top-wrapper d-flex align-items-center justify-content-center"
                        style="height: 140px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);">
                        @if($category->image)
                            <img src="{{ $category->image }}" alt="{{ $category->name }}"
                                style="height: 100px; width: 100px; object-fit: contain; transition: transform 0.3s ease;"
                                onmouseover="this.style.transform='scale(1.1)'" 
                                onmouseout="this.style.transform='scale(1)'">
                        @else
                            <i class="la la-cube font-large-3 text-muted"></i>
                        @endif
                    </div>

                    <div class="card-body">
                        <h5 class="card-title">{{ $category->name }}</h5>
                        {{ Str::limit($category->description, 50) ?? __('admin.messages.no_description') }}
                        </p>

                        <div class="card-actions d-flex flex-nowrap align-items-center justify-content-center w-100" style="gap: 5px; overflow-x: auto; padding-bottom: 5px;">
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="btn btn-sm btn-outline-primary"
                                title="{{ __('admin.buttons.edit') }}">
                                <i class="la la-edit"></i>
                            </a>

                            <a href="{{ route('admin.questions.index', ['category_id' => $category->id]) }}"
                                class="btn btn-sm btn-outline-info" title="{{ __('admin.menu.questions') }}">
                                <i class="la la-question-circle"></i>
                            </a>

                            <a href="{{ route('admin.categories.verification', $category->id) }}"
                                class="btn btn-sm btn-outline-warning" title="{{ __('admin.categories.verification_settings') }}">
                                <i class="la la-shield"></i>
                            </a>

                            @if(!$category->isProtected())
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                    onsubmit="return confirm('{{ __('admin.buttons.confirm_delete') }}');" class="mb-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
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
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h4 class="text-muted">{{ __('admin.categories.no_records') }}</h4>
                        <a href="{{ route('admin.categories.create', isset($parentCategory) ? ['parent_category_id' => $parentCategory->id] : []) }}"
                            class="btn btn-primary mt-2">
                            {{ __('admin.buttons.add_new') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection