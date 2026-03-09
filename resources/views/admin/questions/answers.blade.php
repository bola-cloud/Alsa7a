@extends('layouts.admin')

@section('content')
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">Question Answers</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.questions.index') }}">{{ __('admin.menu.questions') }}</a></li>
                        <li class="breadcrumb-item active">Answers</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Question: <span class="text-primary">{{ $question->getTranslation('question', app()->getLocale()) }}</span>
            </h4>
            <p class="text-muted">Type: {{ __('admin.questions.types.' . $question->type) }}</p>
        </div>
        <div class="card-content">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Category</th>
                                <th>Answer</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($answers as $answer)
                                @php
                                    $val = (is_array($answer->answer) && isset($answer->answer['value'])) 
                                        ? $answer->answer['value'] 
                                        : $answer->answer;
                                    $displayVal = '-';
                                    if ($val !== null) {
                                        if ($question->type == 'boolean') {
                                            $displayVal = ($val == 1 || $val === true || $val === 'true') ? 'Yes' : 'No';
                                            if ($val === 0 || $val === false || $val === 'false')
                                                $displayVal = 'No';
                                        } elseif ($question->type == 'multiple_choice' && is_array($question->choices)) {
                                            $displayVal = $question->choices[$val] ?? $val;
                                        } elseif ($question->type == 'multi_select' && is_array($val) && is_array($question->choices)) {
                                            $labels = [];
                                            foreach ($val as $k) {
                                                $labels[] = $question->choices[$k] ?? $k;
                                            }
                                            $displayVal = implode(', ', $labels);
                                        } elseif (is_array($val)) {
                                            $displayVal = json_encode($val, JSON_UNESCAPED_UNICODE);
                                        } else {
                                            $displayVal = $val;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        @if($answer->user)
                                            <a
                                                href="{{ route('admin.users.show', $answer->user->id) }}">{{ $answer->user->name }}</a>
                                            <br><small>{{ $answer->user->phone }}</small>
                                        @else
                                            <span class="text-danger">Deleted User</span>
                                        @endif
                                    </td>
                                    <td>{{ $answer->user->category->name ?? '-' }}</td>
                                    <td>{{ $displayVal }}</td>
                                    <td>{{ $answer->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No answers found yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $answers->links() }}
            </div>
        </div>
    </div>
@endsection