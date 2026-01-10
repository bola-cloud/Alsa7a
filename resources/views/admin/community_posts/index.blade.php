@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.community_posts.title') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.community_posts.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-12">
                <div class="card admin-card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('admin.buttons.filter') }}</h4>
                        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            <form action="{{ route('admin.community_posts.index') }}" method="GET">
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
                                            <select name="category_id" class="form-control">
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
            @forelse($posts as $post)
                <div class="col-xl-4 col-md-6 col-12">
                    <div class="card admin-card">
                        <div class="card-content">
                            <div class="card-body">
                                <div class="media">
                                    <div class="media-left pr-1">
                                        <span class="avatar avatar-online">
                                            <img src="{{ $post->user->profile_photo_url ?? asset('app-assets/images/portrait/small/avatar-s-1.png') }}"
                                                alt="avatar" style="width: 40px; height: 40px;">
                                        </span>
                                    </div>
                                    <div class="media-body">
                                        <h6 class="media-heading text-bold-700">{{ $post->user->name }}</h6>
                                        <p class="text-muted small">{{ $post->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="media-right">
                                        <span class="badge badge-info">{{ $post->category->name ?? 'General' }}</span>
                                    </div>
                                </div>

                                <p class="mt-1">{{ Str::limit($post->content, 100) }}</p>

                                @if($post->image)
                                    <img src="{{ url('storage/' . $post->image) }}" class="img-fluid rounded mb-1"
                                        style="max-height: 200px; width: 100%; object-fit: cover;">
                                @endif

                                <div class="btn-group btn-group-sm mt-1 w-100">
                                    <form action="{{ route('admin.community_posts.toggle', $post->id) }}" method="POST"
                                        style="width: 50%;">
                                        @csrf
                                        <button type="submit"
                                            class="btn {{ $post->is_hidden ? 'btn-warning' : 'btn-secondary' }} w-100">
                                            <i class="la {{ $post->is_hidden ? 'la-eye-slash' : 'la-eye' }}"></i>
                                            {{ $post->is_hidden ? __('admin.buttons.hidden') : __('admin.buttons.active') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.community_posts.destroy', $post->id) }}" method="POST"
                                        style="width: 50%;"
                                        onsubmit="return confirm('{{ __('admin.messages.confirm_delete') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger w-100">
                                            <i class="la la-trash"></i> {{ __('admin.buttons.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="text-muted">{{ __('admin.categories.no_records') }}</h5>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="row">
            <div class="col-12">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
@endsection