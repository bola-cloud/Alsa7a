@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title mb-0">{{ __('admin.posts.title') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('admin.posts.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('admin.posts.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-9 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.categories.search') }}</label>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control pl-4" placeholder="Search by content or user..." value="{{ request('search') }}">
                            <i class="la la-search position-absolute" style="top: 10px; left: 10px; color: #b0afb5;"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="la la-filter"></i> {{ __('admin.buttons.filter') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="">
        <div class="card shadow-sm border-0">
            <div class="card-content collapse show">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-primary white">
                                <tr>
                                    <th class="border-top-0">#</th>
                                    <th class="border-top-0">{{ __('admin.bookings.name') }}</th>
                                    <th class="border-top-0">Content</th>
                                    <th class="border-top-0">Image</th>
                                    <th class="border-top-0">Status</th>
                                    <th class="border-top-0 text-right">{{ __('admin.buttons.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $post)
                                    <tr>
                                        <td class="align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            @if($post->user)
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm mr-2 bg-primary">
                                                        <span class="avatar-content">{{ substr($post->user->name, 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold">{{ $post->user->name }}</div>
                                                        <small class="text-muted">{{ $post->user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-danger">User Deleted</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">{{ Str::limit($post->content, 60) }}</td>
                                        <td class="align-middle">
                                            @if($post->image)
                                                <a href="{{ asset('storage/' . $post->image) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $post->image) }}" height="50" class="rounded">
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @if($post->is_hidden)
                                                <span class="badge badge-pill badge-danger">Hidden</span>
                                            @else
                                                <span class="badge badge-pill badge-success">Visible</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-right">
                                            <div class="btn-group">
                                                <form action="{{ route('admin.posts.toggle', $post->id) }}" method="POST"
                                                    style="display:inline-block">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $post->is_hidden ? 'btn-outline-success' : 'btn-outline-warning' }} round"
                                                        title="Toggle Visibility">
                                                        <i class="ft-{{ $post->is_hidden ? 'eye' : 'eye-off' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST"
                                                    style="display:inline-block" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger round ml-1"
                                                        title="{{ __('admin.buttons.delete') }}">
                                                        <i class="ft-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center p-3 text-muted">
                                            {{ __('admin.categories.no_records') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-2">
                        {{ $posts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection