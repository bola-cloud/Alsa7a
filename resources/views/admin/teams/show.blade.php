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
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.clubs.teams.index', $club->id) }}">{{ $club->name }} - Teams</a></li>
                        <li class="breadcrumb-item active">{{ $team->name }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ $team->name }} - {{ __('admin.clubs.details') }}</h3>
        </div>
    </div>

    <div class="row">
        <!-- Team Summary -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    @if($team->image)
                        <img src="{{ $team->image }}" class="rounded-circle mb-3"
                            style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #f8f9fa;">
                    @else
                        <div class="avatar avatar-xl bg-secondary mx-auto mb-3">
                            <span class="avatar-content" style="font-size: 2rem;">{{ Str::substr($team->name, 0, 1) }}</span>
                        </div>
                    @endif
                    <h4 class="font-weight-bold mb-1">{{ $team->name }}</h4>
                    <span class="badge badge-primary round px-3 mb-3">{{ $team->sport->name ?? 'No Sport' }}</span>

                    <ul class="list-group list-group-flush text-left border-top mt-2">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">{{ __('admin.clubs.name') }}</span>
                            <span class="font-weight-bold">{{ $club->name }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">{{ __('admin.teams.age_group') }}</span>
                            <span class="font-weight-bold">{{ $team->age_group ?: 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">{{ __('admin.teams.coach') }}</span>
                            <span class="font-weight-bold">{{ $team->coach ?: 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted">{{ __('admin.teams.jersey_color') }}</span>
                            <span class="font-weight-bold">{{ $team->jersey_color ?: 'N/A' }}</span>
                        </li>
                    </ul>

                    <div class="mt-4">
                        <a href="{{ route('admin.clubs.teams.edit', [$club->id, $team->id]) }}"
                            class="btn btn-outline-primary btn-block">
                            <i class="la la-edit"></i> {{ __('admin.buttons.edit') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members (Roster) -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ __('admin.clubs.members') }} <small
                            class="text-muted">({{ $team->members->count() }})</small></h4>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addMemberModal">
                        <i class="la la-plus"></i> {{ __('admin.teams.add_member') }}
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-top-0">{{ __('admin.teams.member') }}</th>
                                    <th class="border-top-0">{{ __('admin.categories.index') }}</th>
                                    <th class="border-top-0">{{ __('admin.teams.joined_at') }}</th>
                                    <th class="border-top-0 text-right">{{ __('admin.buttons.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($team->members as $member)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-2">
                                                    @if($member->profile_photo_url)
                                                        <img src="{{ $member->profile_photo_url }}" class="rounded-circle"
                                                            style="width: 32px; height: 32px; object-fit: cover;">
                                                    @else
                                                        <span class="avatar avatar-sm bg-light text-primary">
                                                            {{ Str::substr($member->name, 0, 1) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold">{{ $member->name }}</div>
                                                    <div class="text-muted small">{{ $member->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light-primary">
                                                {{ $member->category->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>{{ $member->updated_at->format('Y-m-d') }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.users.show', $member->id) }}"
                                                class="btn btn-sm btn-icon btn-white text-info" title="View User">
                                                <i class="la la-eye"></i>
                                            </a>
                                            <form
                                                action="{{ route('admin.clubs.teams.remove_member', [$club->id, $team->id, $member->id]) }}"
                                                method="POST" style="display:inline-block;"
                                                onsubmit="return confirm('Are you sure you want to remove this member from the team?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-icon btn-white text-danger"
                                                    title="Remove Member">
                                                    <i class="la la-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            {{ __('admin.teams.no_members') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" role="dialog" aria-labelledby="addMemberModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel">{{ __('admin.teams.add_member') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.clubs.teams.add_member', [$club->id, $team->id]) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="user_id">{{ __('admin.teams.select_member') }}</label>
                            <select name="user_id" id="user_id" class="form-control select2" required style="width: 100%">
                                <option value="">{{ __('admin.buttons.select') }}...</option>
                                @foreach($candidates as $candidate)
                                    <option value="{{ $candidate->id }}">
                                        {{ $candidate->name }} ({{ $candidate->email }}) -
                                        {{ $candidate->category->name ?? 'Uncategorized' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('admin.buttons.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('admin.buttons.add_new') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection