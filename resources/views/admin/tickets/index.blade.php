@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2 p-1 rounded" style="background-color: #f1f2f6;">
        <div class="content-header-left col-md-6 col-12">
            <h3 class="content-header-title">{{ __('admin.tickets.title') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ __('admin.tickets.title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="">
        <div class="card">
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('admin.tickets.subject') }}</th>
                                    <th>User</th>
                                    <th>{{ __('admin.tickets.priority') }}</th>
                                    <th>Status</th>
                                    <th>{{ __('admin.buttons.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $ticket->subject }}</td>
                                        <td>{{ $ticket->user->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-warning">{{ $ticket->priority }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColor = match ($ticket->status) {
                                                    'open' => 'success',
                                                    'in_progress' => 'info',
                                                    'resolved' => 'primary',
                                                    'closed' => 'secondary',
                                                    default => 'secondary'
                                                };
                                            @endphp
                                            <span
                                                class="badge badge-{{ $statusColor }}">{{ __('admin.status.' . $ticket->status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.tickets.show', $ticket->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="la la-eye"></i>
                                            </a>
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
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection