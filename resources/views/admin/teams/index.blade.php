@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.clubs.index') }}">{{ __('admin.clubs.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ $club->name }} - Teams</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ $club->name }} - {{ __('Teams') }}</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-right mb-3">
            <a href="{{ route('admin.clubs.teams.create', $club->id) }}" class="btn btn-primary shadow-soft">
                <i class="la la-plus"></i> {{ __('admin.clubs.add_new') }} Team
            </a>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom-0">
                    <h4 class="card-title">{{ __('admin.clubs.list') }} Teams</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Name</th>
                                    <th>Sport</th>
                                    <th>Age Group</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teams as $team)
                                    <tr>
                                        <td>{{ $team->id }}</td>
                                        <td>
                                            @if($team->image)
                                                <img src="{{ asset('storage/' . $team->image) }}" class="rounded-circle"
                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <span class="avatar avatar-sm bg-secondary"><span
                                                        class="avatar-content">{{ Str::substr($team->name, 0, 1) }}</span></span>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold">{{ $team->name }}</td>
                                        <td>{{ $team->sport->name ?? 'N/A' }}</td>
                                        <td>{{ $team->age_group }}</td>
                                        <td>
                                            <span class="badge {{ $team->active ? 'badge-success' : 'badge-danger' }}">
                                                {{ $team->active ? __('admin.categories.active') : __('admin.categories.inactive') }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.clubs.teams.edit', [$club->id, $team->id]) }}"
                                                class="btn btn-sm btn-icon btn-white text-primary mr-1"><i
                                                    class="la la-edit"></i></a>
                                            <form action="{{ route('admin.clubs.teams.destroy', [$club->id, $team->id]) }}"
                                                method="POST" style="display:inline-block;"
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
                </div>
            </div>
        </div>
    </div>
@endsection