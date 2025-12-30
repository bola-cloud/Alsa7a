@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h1>{{ $club->name }}</h1>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <img src="{{ $club->logo_url }}" class="img-fluid mb-3" alt="Logo">
                        <p><strong>City:</strong> {{ $club->city }}</p>
                        <p><strong>Country:</strong> {{ $club->country }}</p>
                        <p><strong>Founded:</strong> {{ $club->founded_year }}</p>
                        <p><strong>Website:</strong> <a href="{{ $club->website }}" target="_blank">{{ $club->website }}</a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Roster & Staff</h3>
                    </div>
                    <div class="card-body">
                        @foreach($roster as $category => $members)
                            <h4>{{ $category }}</h4>
                            <div class="row mb-3">
                                @foreach($members as $member)
                                    <div class="col-md-3 text-center">
                                        <img src="{{ $member->profile_photo_url }}" class="img-circle" width="50" height="50">
                                        <p>{{ $member->name }}</p>
                                        @if($member->position)
                                            <small>{{ $member->position }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection