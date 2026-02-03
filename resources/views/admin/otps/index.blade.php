@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('admin.otps.pending_verifications') }}</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="otpTable">
                    <thead>
                        <tr>
                            <th>{{ __('admin.otps.user') }}</th>
                            <th>{{ __('admin.otps.phone') }}</th>
                            <th>{{ __('admin.otps.otp_code') }}</th>
                            <th>{{ __('admin.otps.sent_at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($otpCodes as $code)
                            <tr>
                                <td>
                                    @if($code->user)
                                        <a href="{{ route('admin.users.show', $code->user_id) }}">
                                            {{ $code->user->name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $code->phone }}</td>
                                <td>{{ $code->otp }}</td>
                                <td>{{ $code->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">{{ __('admin.otps.no_pending_codes') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $otpCodes->links() }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#otpTable').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
                "info": false
            });
        });
    </script>
@endsection