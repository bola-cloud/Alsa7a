@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2 p-1 rounded" style="background-color: #f1f2f6;">
        <div class="content-header-left col-md-6 col-12">
            <h3 class="content-header-title">Ticket #{{ $ticket->id }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.tickets.index') }}">{{ __('admin.tickets.title') }}</a></li>
                        <li class="breadcrumb-item active">#{{ $ticket->id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ $ticket->subject }}</h4>
                        <p class="text-muted mb-0">From: {{ $ticket->user->name ?? 'Unknown' }} | Date:
                            {{ $ticket->created_at->format('Y-m-d H:i') }}
                        </p>
                    </div>
                    <div class="card-body">
                        <h5>Message:</h5>
                        <div class="p-2 border rounded bg-light mb-2">
                            {{ $ticket->message }}
                        </div>

                        @if($ticket->ticketable)
                            <div class="alert alert-info">
                                Linked to: {{ class_basename($ticket->ticketable_type) }} #{{ $ticket->ticketable_id }}
                                @if($ticket->ticketable_type === 'App\Models\ServiceRequest')
                                    <a href="{{ route('admin.service_requests.show', $ticket->ticketable_id) }}"
                                        class="btn btn-sm btn-light ml-2">View Request</a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Admin Actions</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="resolved" {{ $ticket->status == 'resolved' ? 'selected' : '' }}>Resolved
                                    </option>
                                    <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Admin Notes</label>
                                <textarea name="admin_notes" class="form-control"
                                    rows="4">{{ $ticket->admin_notes }}</textarea>
                            </div>

                            <button type="submit"
                                class="btn btn-primary btn-block">{{ __('admin.buttons.update') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection