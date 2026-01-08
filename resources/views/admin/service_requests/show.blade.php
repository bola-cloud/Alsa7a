@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2 p-1 rounded" style="background-color: #f1f2f6;">
        <div class="content-header-left col-md-6 col-12">
            <h3 class="content-header-title">Request #{{ $request->id }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.service_requests.index') }}">{{ __('admin.service_requests.title') }}</a>
                        </li>
                        <li class="breadcrumb-item active">#{{ $request->id }}</li>
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
                        <h4 class="card-title">Details</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>Service</th>
                                <td><a
                                        href="{{ route('admin.services.show', $request->service_id) }}">{{ $request->service->title ?? 'Deleted Service' }}</a>
                                </td>
                            </tr>
                            <tr>
                                <th>Requester</th>
                                <td>{{ $request->requester->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Provider</th>
                                <td>{{ $request->provider->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Scheduled At</th>
                                <td>{{ $request->scheduled_at }}</td>
                            </tr>
                            <tr>
                                <th>Ends At</th>
                                <td>{{ $request->end_at ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Message</th>
                                <td>{{ $request->message }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Status & Payment</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <label class="font-weight-bold">Status:</label>
                            <span class="badge badge-info">{{ __('admin.status.' . $request->status) }}</span>
                        </div>
                        <div class="mb-2">
                            <label class="font-weight-bold">Is Disputed:</label>
                            @if($request->is_disputed)
                                <span class="badge badge-danger">YES</span>
                            @else
                                <span class="badge badge-success">No</span>
                            @endif
                        </div>
                        <hr>
                        <div class="mb-2">
                            <label class="font-weight-bold">Price:</label> {{ $request->price }}
                        </div>
                        <div class="mb-2">
                            <label class="font-weight-bold">Payment Status:</label>
                            <span class="badge badge-secondary">{{ __('admin.status.' . $request->payment_status) }}</span>
                        </div>
                        <div class="mb-2">
                            <label class="font-weight-bold">Transaction ID:</label> <br>
                            <code>{{ $request->payment_transaction_id ?? 'N/A' }}</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection