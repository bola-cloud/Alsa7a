@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.users.details') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.users.index') }}">{{ __('admin.users.title') }}</a></li>
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
                    <h4 class="card-title">{{ __('admin.users.profile_info') }}</h4>
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
                                {{ __('admin.users.name') }}
                                <span class="font-weight-bold">{{ $user->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('admin.users.email') }}
                                <span>{{ $user->email }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('admin.users.phone') }}
                                <div>
                                    <span>{{ $user->phone }}</span>
                                    @if($user->phone_verified_at)
                                        <i class="la la-check-circle text-success" title="{{ __('admin.users.verified') }}"></i>
                                    @else
                                        <form action="{{ route('admin.users.verify_phone', $user->id) }}" method="POST"
                                            class="d-inline-block ml-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger py-0 px-1"
                                                title="{{ __('admin.users.force_verify') }}"
                                                onclick="return confirm('{{ __('admin.users.confirm_verify_phone') }}')">
                                                {{ __('admin.users.verify') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('admin.users.role_category') }}
                                <span class="badge badge-primary">{{ $user->category->name ?? 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('admin.users.verification') }}
                                @if($user->verification_status == 'approved')
                                    <span class="badge badge-success">{{ __('admin.users.verified') }}</span>
                                @else
                                    <span class="badge badge-warning">{{ ucfirst($user->verification_status) }}</span>
                                @endif
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ __('admin.users.status') }}
                                <div>
                                    @if($user->is_approved)
                                        <span class="badge badge-success">{{ __('admin.users.approved') }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ __('admin.users.pending') }}</span>
                                    @endif

                                    @if($user->is_blocked)
                                        <span class="badge badge-danger">{{ __('admin.users.block') }}</span>
                                    @endif
                                </div>
                            </li>
                            @if($user->email !== 'admin@alsa7a.com')
                                <li class="list-group-item">
                                    <form action="{{ route('admin.users.toggle_block', $user->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="btn btn-sm btn-block {{ $user->is_blocked ? 'btn-success' : 'btn-warning' }}">
                                            <i class="la {{ $user->is_blocked ? 'la-unlock' : 'la-ban' }}"></i>
                                            {{ $user->is_blocked ? __('admin.users.unblock') : __('admin.users.block') }}
                                        </button>
                                    </form>
                                </li>
                            @endif
                        </ul>

                        @if($user->verification_status == 'pending')
                            <hr>
                            <h5 class="mt-2">{{ __('admin.users.doc_verification') }}</h5>
                            @if($user->verification_documents)
                                @foreach($user->verification_documents as $doc)
                                    <a href="{{ url('storage/' . $doc) }}" target="_blank"
                                        class="btn btn-sm btn-outline-info mb-1">{{ __('admin.users.view_document') }}</a>
                                @endforeach
                            @else
                                <p class="text-muted text-sm">{{ __('admin.users.no_docs') }}</p>
                            @endif

                            <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="mt-2">
                                @csrf
                                <div class="form-group">
                                    <select name="status" class="form-control form-control-sm mb-1">
                                        <option value="approved">{{ __('admin.users.approve') }}</option>
                                        <option value="rejected">{{ __('admin.users.reject') }}</option>
                                    </select>
                                    <input type="text" name="rejection_reason" class="form-control form-control-sm mt-1"
                                        placeholder="{{ __('admin.users.reason') }}">
                                </div>
                                <button type="submit"
                                    class="btn btn-sm btn-block btn-primary">{{ __('admin.users.update_status') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.users.questions_answers') }}</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        @if($questions->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('admin.questions.question') }}</th>
                                            <th>{{ __('admin.questions.type') }}</th>
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
                                                $val = null;
                                                if ($answer) {
                                                    // Handle both flat format and {"value": ...} format
                                                    $val = (is_array($answer->answer) && isset($answer->answer['value'])) 
                                                        ? $answer->answer['value'] 
                                                        : $answer->answer;
                                                }
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

                                                    // Handle multi-select or other array values to prevent htmlspecialchars error
                                                    if (is_array($displayVal)) {
                                                        $displayVal = implode(', ', $displayVal);
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $question->question[app()->getLocale()] ?? $question->question['en'] ?? json_encode($question->question) }}
                                                </td>
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
                            <div class="alert alert-info">{{ __('admin.users.no_questions') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection