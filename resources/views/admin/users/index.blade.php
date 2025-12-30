@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h1>{{ __('User Management') }}</h1>

        <div class="mb-3">
            <a href="{{ route('admin.users.index') }}" class="btn btn-default">All</a>
            <a href="{{ route('admin.users.index', ['pending_approval' => 1]) }}" class="btn btn-warning">Pending
                Approval</a>
            <a href="{{ route('admin.users.index', ['pending_verification' => 1]) }}" class="btn btn-info">Pending Document
                Verification</a>
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