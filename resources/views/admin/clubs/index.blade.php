@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">{{ __('Clubs') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Clubs') }}</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <a href="{{ route('admin.clubs.create') }}" class="btn btn-primary">{{ __('Add New Club') }}</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                                <td>{{ $club->name }}</td>
                                <td>{{ $club->city }}</td>
                                <td>
                                    @foreach($club->sports as $sport)
                                        <span class="badge badge-info">{{ $sport->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('admin.clubs.show', $club->id) }}" class="btn btn-info btn-sm">View</a>
                                    <a href="{{ route('admin.clubs.edit', $club->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('admin.clubs.destroy', $club->id) }}" method="POST"
                                        style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $clubs->links() }}
            </div>
        </div>
    </div>
@endsection