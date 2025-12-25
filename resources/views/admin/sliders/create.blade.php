@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card" style="border-radius: 20px; box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.sliders.create') }}</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">
                        <form class="form" action="{{ route('admin.sliders.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title">{{ __('admin.sliders.title') }}</label>
                                            <input type="text" id="title" class="form-control round" name="title">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="image">{{ __('admin.sliders.image') }}</label>
                                            <input type="file" id="image" class="form-control-file" name="image" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions text-right">
                                <a href="{{ route('admin.sliders.index') }}" class="btn btn-warning mr-1 rounded-pill">
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