@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">User Details</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">{{ $user->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Profile Info</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="text-center mb-2">
                            @if($user->profile_photo_url)
                                <img src="{{ $user->profile_photo_url }}" class="rounded-circle img-thumbnail"
                                    style="width: 100px; height: 100px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto"
                                    style="width: 100px; height: 100px; color: white; font-size: 24px;">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Name
                                <span class="font-weight-bold">{{ $user->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Email
                                <span>{{ $user->email }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Phone
                                <span>{{ $user->phone }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Role/Category
                                <span class="badge badge-primary">{{ $user->category->name ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Verified
                                @if($user->verification_status == 'approved')
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($user->verification_status) }}</span>
                                @endif
                            </li>
                        </ul>

                        @if($user->verification_status == 'pending')
                            <hr>
                            <h5 class="mt-2">Verification Documents</h5>
                            @if($user->verification_documents)
                                @foreach($user->verification_documents as $doc)
                                    <a href="{{ $doc }}" target="_blank" class="btn btn-sm btn-outline-info mb-1">View Document</a>
                                @endforeach
                            @else
                                <p class="text-muted text-sm">No documents uploaded.</p>
                            @endif

                            <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="mt-2">
                                @csrf
                                <div class="form-group">
                                    <select name="status" class="form-control form-control-sm mb-1">
                                        <option value="approved">Approve</option>
                                        <option value="rejected">Reject</option>
                                    </select>
                                    <input type="text" name="rejection_reason" class="form-control form-control-sm mt-1"
                                        placeholder="Reason (if rejected)">
                                </div>
                                <button type="submit" class="btn btn-sm btn-block btn-primary">Update Status</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Questions & Answers</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        @if($questions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Question</th>
                                            <th>Type</th>
                                            <th>Answer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // Key retrieval by question_id for faster lookup
                                            $userAnswers = $user->answers->keyBy('question_id');
                                        @endphp
                                        @foreach($questions as $question)
                                            @php
                                                $answer = $userAnswers->get($question->id);
                                                $val = $answer ? ($answer->answer['value'] ?? null) : null;
                                                $displayVal = '-';

                                                if ($val !== null) {
                                                    if ($question->type == 'boolean') {
                                                        $displayVal = ($val == 1 || $val === true || $val === 'true') ? 'Yes' : 'No';
                                                        if ($val === 0 || $val === false || $val === 'false')
                                                            $displayVal = 'No';
                                                    } elseif ($question->type == 'multiple_choice' && is_array($question->choices)) {
                                                        // Try to match key to label
                                                        $displayVal = $question->choices[$val] ?? $val;
                                                    } else {
                                                        $displayVal = $val;
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $question->question }}</td>
                                                <td><span
                                                        class="badge badge-info">{{ __('admin.questions.types.' . $question->type) }}</span>
                                                </td>
                                                <td>{{ $displayVal }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">No questions found for this user's category.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection