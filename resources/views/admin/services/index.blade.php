@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2 p-1 rounded" style="background-color: #f1f2f6;">
        <div class="content-header-left col-md-6 col-12">
            <h3 class="content-header-title">{{ __('admin.services.title') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('admin.services.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('admin.services.title') }}</th>
                                    <th>{{ __('admin.services.provider') }}</th>
                                    <th>{{ __('admin.services.price') }}</th>
                                    <th>{{ __('admin.sports.active') }}</th>
                                    <th>{{ __('admin.buttons.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $service->title }}</td>
                                        <td>{{ $service->provider->name ?? '-' }}</td>
                                        <td>{{ $service->price }} {{ $service->currency }}</td>
                                        <td>
                                            <span class="badge badge-{{ $service->is_active ? 'success' : 'danger' }}">
                                                {{ $service->is_active ? __('admin.categories.yes') : __('admin.categories.no') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.services.show', $service->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="la la-eye"></i>
                                            </a>
                                            <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('{{ __('admin.buttons.confirm_delete') }}')">
                                                    <i class="la la-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('admin.categories.no_records') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $services->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection