@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.users.create') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.users.index') }}">{{ __('admin.users.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.users.add_user') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form class="form" action="{{ route('admin.users.store') }}" method="POST">
                            @csrf
                            <div class="form-body">
                                <div class="form-group">
                                    <label>{{ __('admin.users.name') }}</label>
                                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.users.email') }}</label>
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                        required>
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.users.phone') }}</label>
                                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                                        required>
                                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.users.password') }}</label>
                                            <input type="password" class="form-control" name="password" required
                                                minlength="8">
                                            @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.users.confirm_password') }}</label>
                                            <input type="password" class="form-control" name="password_confirmation"
                                                required minlength="8">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.users.role_category') }}</label>
                                    <select name="category_id" class="form-control">
                                        <option value="">User (Standard)</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="is_admin" id="isAdmin"
                                            value="1" {{ old('is_admin') ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                            for="isAdmin">{{ __('admin.users.is_admin') }}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" name="is_approved"
                                            id="isApproved" value="1" {{ old('is_approved') ? 'checked' : '' }}>
                                        <label class="custom-control-label"
                                            for="isApproved">{{ __('admin.users.is_approved') }}</label>
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions right">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-warning mr-1">
                                    <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="la la-check-square-o"></i> {{ __('admin.buttons.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            $('#club_id').on('change', function () {
                var clubId = $(this).val();
                var teamSelect = $('#team_id');
                teamSelect.html('<option value="">Loading...</option>');

                if (clubId) {
                    var url = "{{ route('admin.clubs.teams_json', ':id') }}";
                    url = url.replace(':id', clubId);

                    $.get(url, function (data) {
                        teamSelect.html('<option value="">{{ __("admin.buttons.select") }}</option>');
                        $.each(data, function (key, team) {
                            teamSelect.append('<option value="' + team.id + '">' + team.name + '</option>');
                        });
                    });
                } else {
                    teamSelect.html('<option value="">{{ __("admin.buttons.select") }}</option>');
                }
            });
        });
    </script>
@endpush