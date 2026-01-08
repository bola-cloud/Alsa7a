@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2 p-1 rounded" style="background-color: #f1f2f6;">
        <div class="content-header-left col-md-6 col-12">
            <h3 class="content-header-title">{{ __('admin.service_requests.title') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.service_requests.title') }}</li>
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
                                    <th>Service</th>
                                    <th>{{ __('admin.service_requests.requester') }}</th>
                                    <th>{{ __('admin.services.provider') }}</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>{{ __('admin.buttons.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requests as $req)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $req->service->title ?? '-' }}</td>
                                        <td>{{ $req->requester->name ?? '-' }}</td>
                                        <td>{{ $req->provider->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ __('admin.status.' . $req->status) }}</span>
                                            @if($req->is_disputed)
                                                <span class="badge badge-danger">Disputed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ __('admin.status.' . $req->payment_status) }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.service_requests.show', $req->id) }}" class="btn btn-sm btn-info">
                                                <i class="la la-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('admin.categories.no_records') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
