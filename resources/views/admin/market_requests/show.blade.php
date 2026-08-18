@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.market_requests.index') }}">{{ __('admin.market_requests.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ Str::limit($marketRequest->title, 20) }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.market_requests.title') }}</h3>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="bg-light rounded-circle mb-3 d-flex align-items-center justify-content-center mx-auto"
                        style="width: 70px; height: 70px;">
                        <i class="la la-suitcase font-large-2 text-muted"></i>
                    </div>

                    <h4 class="card-title font-weight-bold">{{ $marketRequest->title }}</h4>
                    <p class="text-muted mb-2">
                        {{ $marketRequest->category ? (app()->getLocale() == 'ar' ? $marketRequest->category->name_ar : $marketRequest->category->name_en) : '-' }}
                    </p>

                    <div class="mb-3">
                        <span class="badge badge-pill badge-{{ $marketRequest->status === 'active' ? 'success' : 'secondary' }} px-3 py-1">
                            {{ $marketRequest->status === 'active' ? __('admin.market_requests.active') : __('admin.market_requests.closed') }}
                        </span>
                    </div>

                    <div class="row text-left mt-4">
                        <div class="col-12 py-2 border-bottom">
                            <strong>{{ __('admin.market_requests.poster') }}:</strong>
                            <span class="float-right">{{ $marketRequest->user->name ?? '-' }}</span>
                        </div>
                        <div class="col-12 py-2 border-bottom">
                            <strong>{{ __('admin.market_requests.club') }}:</strong>
                            <span class="float-right">{{ $marketRequest->club->name ?? __('admin.market_requests.no_club') }}</span>
                        </div>
                        <div class="col-12 py-2 border-bottom">
                            <strong>{{ __('admin.market_requests.applicants_count') }}:</strong>
                            <span class="float-right">{{ $marketRequest->applications->count() }}</span>
                        </div>
                        <div class="col-12 py-2 border-bottom">
                            <strong>{{ __('admin.market_requests.date') }}:</strong>
                            <span class="float-right">{{ $marketRequest->created_at?->format('Y-m-d H:i') }}</span>
                        </div>
                        @if($marketRequest->scheduled_at)
                            <div class="col-12 py-2 border-bottom">
                                <strong>{{ __('admin.market_requests.scheduled_at') }}:</strong>
                                <span class="float-right">{{ $marketRequest->scheduled_at->format('Y-m-d H:i') }}</span>
                            </div>
                        @endif
                        @if($marketRequest->cost !== null)
                            <div class="col-12 py-2 border-bottom">
                                <strong>{{ __('admin.market_requests.cost') }}:</strong>
                                <span class="float-right">{{ $marketRequest->cost }} {{ __('admin.market_requests.currency') }}</span>
                            </div>
                        @endif
                        @if($marketRequest->address)
                            <div class="col-12 py-2 border-bottom">
                                <strong>{{ __('admin.market_requests.address') }}:</strong>
                                <span class="float-right">{{ $marketRequest->address }}</span>
                            </div>
                        @endif
                        @if($marketRequest->latitude && $marketRequest->longitude)
                            <div class="col-12 py-2 border-bottom">
                                <strong>{{ __('admin.market_requests.location') }}:</strong>
                                <span class="float-right">
                                    <a href="https://maps.google.com/?q={{ $marketRequest->latitude }},{{ $marketRequest->longitude }}"
                                        target="_blank" rel="noopener">
                                        <i class="la la-map-marker"></i>
                                        {{ $marketRequest->latitude }}, {{ $marketRequest->longitude }}
                                    </a>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h4 class="card-title">{{ __('admin.market_requests.description') }}</h4>
                </div>
                <div class="card-body">
                    <p class="text-dark">{{ $marketRequest->description }}</p>
                </div>
                <div class="card-footer bg-white border-top-0 text-right pb-4">
                    <a href="{{ route('admin.market_requests.index') }}" class="btn btn-outline-secondary mr-2">
                        <i class="la la-arrow-left"></i> {{ __('admin.buttons.back') }}
                    </a>
                    <form action="{{ route('admin.market_requests.update', $marketRequest->id) }}" method="POST"
                        style="display:inline-block"
                        onsubmit="return confirm('{{ $marketRequest->status === 'active' ? __('admin.market_requests.confirm_close') : __('admin.market_requests.confirm_reopen') }}');">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="{{ $marketRequest->status === 'active' ? 'closed' : 'active' }}">
                        <button type="submit" class="btn {{ $marketRequest->status === 'active' ? 'btn-warning' : 'btn-success' }}">
                            <i class="la {{ $marketRequest->status === 'active' ? 'la-lock' : 'la-unlock' }}"></i>
                            {{ $marketRequest->status === 'active' ? __('admin.market_requests.close') : __('admin.market_requests.reopen') }}
                        </button>
                    </form>
                </div>
            </div>

            @if($marketRequest->questions->isNotEmpty())
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h4 class="card-title">
                            {{ __('admin.market_requests.questions') }}
                            <span class="badge badge-light border">{{ $marketRequest->questions->count() }}</span>
                        </h4>
                    </div>
                    <div class="card-body">
                        @foreach($marketRequest->questions as $question)
                            <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="font-weight-bold text-dark">
                                    {{ $loop->iteration }}. {{ $question->question }}
                                    @if($question->is_required)
                                        <span class="badge badge-danger ml-1">{{ __('admin.market_requests.required') }}</span>
                                    @else
                                        <span class="badge badge-secondary ml-1">{{ __('admin.market_requests.optional') }}</span>
                                    @endif
                                </div>
                                <div class="mt-2">
                                    @foreach((array) $question->options as $option)
                                        <span class="badge badge-light border mr-1 mb-1 px-2 py-1">{{ $option }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h4 class="card-title">
                        {{ __('admin.market_requests.applicants') }}
                        <span class="badge badge-light border">{{ $marketRequest->applications->count() }}</span>
                    </h4>
                </div>
                <div class="card-body">
                    @forelse($marketRequest->applications as $application)
                        @php($applicant = $application->user)
                        <div class="border rounded p-3 mb-3" style="border-radius: 12px !important;">
                            <div class="d-flex align-items-start">
                                @if($applicant && $applicant->profile_photo_path)
                                    <img src="{{ asset('storage/' . $applicant->profile_photo_path) }}"
                                        style="width:56px;height:56px;object-fit:cover;border-radius:50%;" class="mr-3">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center mr-3"
                                        style="width:56px;height:56px;border-radius:50%;">
                                        <i class="la la-user font-medium-4 text-muted"></i>
                                    </div>
                                @endif

                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="font-weight-bold text-dark">{{ $applicant->name ?? '-' }}</span>
                                            @if($applicant && $applicant->verification_status === 'approved')
                                                <i class="la la-check-circle text-success" title="{{ __('admin.users.verified') }}"></i>
                                            @endif
                                            @if($applicant && $applicant->category)
                                                <span class="badge badge-primary ml-1">{{ $applicant->category->name }}</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">{{ $applicant->phone ?? '' }}</small>
                                        </div>
                                        <div class="text-right">
                                            <small class="text-muted d-block">{{ $application->created_at?->format('Y-m-d H:i') }}</small>
                                            @if($application->cv_path)
                                                <a href="{{ asset('storage/' . $application->cv_path) }}" target="_blank"
                                                    class="btn btn-sm btn-outline-primary round mt-1">
                                                    <i class="la la-file-pdf-o"></i> {{ __('admin.market_requests.view_cv') }}
                                                </a>
                                            @endif
                                            @if($applicant)
                                                <a href="{{ route('admin.users.show', $applicant->id) }}"
                                                    class="btn btn-sm btn-outline-info round mt-1">
                                                    <i class="la la-user"></i> {{ __('admin.buttons.view') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Professional details inline, so the whole judgement happens on this page --}}
                                    @if($applicant)
                                        @php($club = $applicant->ownedClub ?: $applicant->club)
                                        @php($ratings = $applicant->ratingsReceived)
                                        <div class="mt-2 small text-muted">
                                            @if($club)<span class="mr-3"><i class="la la-shield"></i> {{ $club->name }}</span>@endif
                                            @if($applicant->position)<span class="mr-3"><i class="la la-map-marker"></i> {{ $applicant->position }}</span>@endif
                                            @if($applicant->number)<span class="mr-3"><i class="la la-hashtag"></i> {{ $applicant->number }}</span>@endif
                                            @if($applicant->nationality)<span class="mr-3"><i class="la la-flag"></i> {{ $applicant->nationality }}</span>@endif
                                            @if($ratings && $ratings->count())
                                                <span class="mr-3"><i class="la la-star text-warning"></i> {{ round($ratings->avg('rating'), 1) }} ({{ $ratings->count() }})</span>
                                            @endif
                                        </div>
                                        @if($applicant->bio)
                                            <div class="mt-2 small text-dark">{{ $applicant->bio }}</div>
                                        @endif
                                    @endif

                                    @if($application->notes)
                                        <div class="mt-2">
                                            <span class="text-muted small">{{ __('admin.market_requests.notes') }}:</span>
                                            <span class="small">{{ $application->notes }}</span>
                                        </div>
                                    @endif

                                    {{-- Answers to this request's own questions --}}
                                    @if($application->answers->isNotEmpty())
                                        <div class="mt-3 pt-2 border-top">
                                            @foreach($application->answers as $answer)
                                                <div class="small mb-1">
                                                    <span class="text-muted">{{ optional($answer->question)->question }}:</span>
                                                    <span class="font-weight-bold text-dark">{{ $answer->answer }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Their registration Q&A --}}
                                    @if($applicant && $applicant->answers->isNotEmpty())
                                        <div class="mt-2 pt-2 border-top">
                                            @foreach($applicant->answers->take(6) as $answer)
                                                @continue(!$answer->question)
                                                @php($q = $answer->question->question)
                                                {{-- Answers come in several shapes: a plain string, a list, or
                                                     {"value": ...} where value may itself be a list. Flatten
                                                     whatever it is instead of assuming. --}}
                                                @php($a = collect(\Illuminate\Support\Arr::flatten([$answer->answer]))
                                                        ->filter(fn ($v) => !is_null($v) && $v !== '')
                                                        ->map(fn ($v) => is_bool($v) ? ($v ? __('admin.categories.yes') : __('admin.categories.no')) : (string) $v)
                                                        ->implode(', '))
                                                <div class="small mb-1">
                                                    <span class="text-muted">{{ is_array($q) ? ($q['ar'] ?? $q['en'] ?? '') : $q }}:</span>
                                                    <span>{{ $a }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center p-3 text-muted">
                            {{ __('admin.market_requests.no_applicants') }}
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection
