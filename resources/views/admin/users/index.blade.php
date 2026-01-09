@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h1>{{ __('User Management') }}</h1>

        <div class="mb-3">
             <div class="row">
                 <div class="col-md-6 mb-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default mr-1">All</a>
                    <a href="{{ route('admin.users.index', ['pending_approval' => 1]) }}" class="btn btn-outline-warning mr-1">Pending Approval</a>
                    <a href="{{ route('admin.users.index', ['pending_verification' => 1]) }}" class="btn btn-outline-info">Doc Verification</a>
                 </div>
             </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body p-3">
                <form action="{{ route('admin.users.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="text-muted small mb-1">{{ __('Search') }}</label>
                            <div class="position-relative">
                                <input type="text" name="search" class="form-control pl-4" placeholder="Name, Email, Phone..." value="{{ request('search') }}">
                                <i class="la la-search position-absolute" style="top: 10px; left: 10px; color: #b0afb5;"></i>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="text-muted small mb-1">{{ __('Role/Category') }}</label>
                            <select name="category_id" class="form-control">
                                <option value="">All Roles</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="la la-filter"></i> {{ __('Filter') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body pl-0 pr-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Verification</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <div>{{ $user->name }}</div>
                                        <small class="text-muted">{{ $user->email }} / {{ $user->phone }}</small>
                                    </td>
                                    <td>{{ $user->category->name ?? 'User' }}</td>
                                    <td>
                                        @if($user->is_approved)
                                            <span class="badge badge-success">Approved</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->verification_status === 'approved')
                                            <span class="badge badge-success">Verified</span>
                                        @elseif($user->verification_status === 'pending')
                                            <span class="badge badge-warning">Docs Pending</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($user->verification_status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user->id) }}"
                                            class="btn btn-sm btn-primary">Manage</a>

                                        @if(!$user->is_approved)
                                            <form action="{{ route('admin.users.approve', $user->id) }}" method="POST"
                                                style="display:inline-block;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Approve Access</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $users->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection