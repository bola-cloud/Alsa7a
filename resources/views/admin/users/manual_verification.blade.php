@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ __('admin.otps.force_verification') }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="form-group">
                        <label>{{ __('admin.users.search_placeholder') }}</label>
                        <select id="userSelect" class="form-control select2" style="width: 100%"></select>
                    </div>

                    <div id="userInfo" class="mt-4 text-center d-none">
                        <div class="card border p-3">
                            <img id="userImage" src="" class="rounded-circle mx-auto d-block mb-3"
                                style="width: 100px; height: 100px; object-fit: cover;">
                            <h4 id="userName"></h4>
                            <p id="userPhone" class="text-muted"></p>
                            <p id="userStatus"></p>

                            <div id="actionArea" class="mt-3">
                                <form id="verifyForm" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block"
                                        onclick="return confirm('{{ __('admin.users.confirm_verify_phone') }}')">
                                        {{ __('admin.users.force_verify') }}
                                    </button>
                                </form>
                                <div id="verifiedBadge" class="d-none">
                                    <span class="badge badge-success p-2">{{ __('admin.users.verified') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function () {
        $('#userSelect').select2({
            placeholder: "{{ __('admin.users.search_placeholder') }}",
            allowClear: true,
            dir: "{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}",
            minimumInputLength: 2,
            ajax: {
                url: "{{ route('admin.users.search') }}",
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        $('#userSelect').on('select2:select', function (e) {
            var data = e.params.data;
            var user = data.user;

            $('#userInfo').removeClass('d-none');
            $('#userName').text(user.name);
            $('#userPhone').text(user.phone);
            $('#userImage').attr('src', user.profile_photo_url || 'https://ui-avatars.com/api/?name=' + user.name);

            // Verification Status
            var verified = user.phone_verified_at != null;

            if (verified) {
                $('#userStatus').html('<span class="badge badge-success">{{ __('admin.users.verified') }}</span>');
                $('#verifyForm').addClass('d-none');
                $('#verifiedBadge').removeClass('d-none');
            } else {
                $('#userStatus').html('<span class="badge badge-warning">{{ __('admin.users.pending') }}</span>');
                $('#verifyForm').removeClass('d-none');
                $('#verifiedBadge').addClass('d-none');

                // Construct Verify Route
                var actionUrl = "{{ route('admin.users.verify_phone', ':id') }}"; // Placeholder
                actionUrl = actionUrl.replace(':id', user.id);
                $('#verifyForm').attr('action', actionUrl);
            }
        });

        $('#userSelect').on('select2:unselect', function (e) {
            $('#userInfo').addClass('d-none');
        });
    });
</script>
@endpush