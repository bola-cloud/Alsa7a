@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.categories.create') }}</h4>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">{{ __('admin.categories.name') }}</label>
                                            <input type="text" id="name" class="form-control round" name="name" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">{{ __('admin.categories.image') }}</label>
                                            <input type="file" id="image" class="form-control-file" name="image">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="description">{{ __('admin.categories.description') }}</label>
                                            <textarea id="description" class="form-control round" name="description"
                                                rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <fieldset>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input"
                                                        name="is_service_provider" id="is_service_provider" value="1">
                                                    <label class="custom-control-label"
                                                        for="is_service_provider">{{ __('admin.categories.is_service_provider') }}</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions text-right">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-warning mr-1 rounded-pill">
                                    <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary rounded-pill">
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