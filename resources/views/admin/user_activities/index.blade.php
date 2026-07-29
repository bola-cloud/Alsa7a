@extends('layouts.admin')

@section('content')
<div class="content-header row mb-2">
    <div class="content-header-left col-md-6 col-12 mb-2">
        <h3 class="content-header-title text-bold-700">{{ __('admin.activities.title') }}</h3>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.activities.user') }}</th>
                                    <th>{{ __('admin.activities.action') }}</th>
                                    <th>{{ __('admin.activities.details') }}</th>
                                    <th>{{ __('admin.activities.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities as $activity)
                                    <tr>
                                        <td>{{ $activity->user->name ?? __('admin.activities.deleted_user') }}</td>
                                        <td><span class="badge badge-info">{{ $activity->action }}</span></td>
                                        <td>
                                            @if($activity->details)
                                                <pre style="margin: 0; font-size: 11px;">{{ json_encode($activity->details, JSON_UNESCAPED_UNICODE) }}</pre>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">{{ __('admin.activities.no_activities') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $activities->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
