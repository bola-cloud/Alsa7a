@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('Create Club') }}</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.clubs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Name (English)</label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Name (Arabic)</label>
                        <input type="text" name="name_ar" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Logo</label>
                        <input type="file" name="logo" class="form-control-file">
                    </div>
                    <div class="form-group">
                        <label>Sports</label>
                        <select name="sports[]" class="form-control" multiple>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Save</button>
                </form>
            </div>
        </div>
    </div>
@endsection