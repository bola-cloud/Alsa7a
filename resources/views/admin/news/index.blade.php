@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title mb-0">{{ __('admin.news.title') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('admin.news.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <a href="{{ route('admin.news.create') }}" class="btn btn-info btn-glow round px-2">
                    <i class="ft-plus icon-left"></i> {{ __('admin.buttons.add_new') }}
                </a>
            </div>
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
                                    <th class="border-top-0">{{ __('admin.categories.image') }}</th>
                                    <th class="border-top-0">{{ __('admin.categories.name') }}</th>
                                    <th class="border-top-0">{{ __('admin.sports.title') }}</th>
                                    <th class="border-top-0 text-right">{{ __('admin.buttons.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($news as $item)
                                    <tr>
                                        <td class="align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">
                                            @if($item->featured_image)
                                                <div class="avatar avatar-lg">
                                                    <img src="{{ $item->featured_image }}" alt="{{ $item->title_en }}"
                                                        class="img-fluid rounded">
                                                </div>
                                            @else
                                                <div class="avatar avatar-lg bg-light-secondary">
                                                    <span class="avatar-content"><i class="ft-image"></i></span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle font-weight-bold">{{ $item->getAttribute('title') }}</td>
                                        <td class="align-middle">
                                            @if($item->sport)
                                                <span class="badge badge-pill badge-info">{{ $item->sport->name }}</span>
                                            @else
                                                <span
                                                    class="badge badge-pill badge-secondary">{{ __('admin.categories.no') }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-right">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.news.edit', $item->id) }}"
                                                    class="btn btn-sm btn-outline-primary round"
                                                    title="{{ __('admin.buttons.edit') }}">
                                                    <i class="ft-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.news.destroy', $item->id) }}" method="POST"
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
                                        <td colspan="5" class="text-center p-3 text-muted">
                                            {{ __('admin.categories.no_records') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-2">
                        {{ $news->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection