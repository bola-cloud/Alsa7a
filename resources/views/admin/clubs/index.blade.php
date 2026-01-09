@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Clubs') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('Clubs') }}</h3>
        </div>
        <div class="content-header-right col-md-6 col-12 text-right">
            <a href="{{ route('admin.clubs.create') }}" class="btn btn-primary shadow-soft">
                <i class="la la-plus"></i> {{ __('Add New Club') }}
            </a>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-3">
            <form action="{{ route('admin.clubs.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.categories.search') }}</label>
                        <div class="position-relative">
                            <input type="text" name="search" class="form-control pl-4" placeholder="{{ __('Name') }}..."
                                value="{{ request('search') }}">
                            <i class="la la-search position-absolute" style="top: 10px; left: 10px; color: #b0afb5;"></i>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="text-muted small mb-1">{{ __('admin.sports.title') }}</label>
                        <select name="sport_id" class="form-control">
                            <option value="">{{ __('admin.categories.all') }}</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}" {{ request('sport_id') == $sport->id ? 'selected' : '' }}>
                                    {{ $sport->name }}
                                </option>
                            @endforeach
                        </select>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom-0">
                    <h4 class="card-title">{{ __('Clubs List') }}</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>City</th>
                                    <th>Sports</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($clubs as $club)
                                    <tr>
                                        <td>{{ $club->id }}</td>
                                        <td>
                                            @if($club->logo_url)
                                                <img src="{{ $club->logo_url }}" alt="Logo"
                                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                            @else
                                                <span class="avatar avatar-sm bg-secondary">
                                                    <span class="avatar-content">{{ Str::substr($club->name, 0, 1) }}</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold">{{ $club->name }}</td>
                                        <td>{{ $club->city }}</td>
                                        <td>
                                            @foreach($club->sports as $sport)
                                                <span class="badge badge-light-primary round mb-1">{{ $sport->name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.clubs.edit', $club->id) }}"
                                                class="btn btn-sm btn-icon btn-white text-primary mr-1"><i
                                                    class="la la-edit"></i></a>
                                            <form action="{{ route('admin.clubs.destroy', $club->id) }}" method="POST"
                                                style="display:inline-block;"
                                                onsubmit="return confirm('{{ __('admin.messages.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-white text-danger"><i
                                                        class="la la-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">
                        {{ $clubs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection