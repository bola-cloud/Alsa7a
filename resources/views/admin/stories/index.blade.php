@extends('layouts.admin')

@section('content')
<div class="content-header row mb-2">
    <div class="content-header-left col-md-6 col-12 mb-2">
        <h3 class="content-header-title text-bold-700">{{ __('admin.menu.stories') ?? 'Stories' }}</h3>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card modern-card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('admin.users.id') }}</th>
                                    <th>{{ __('admin.users.name') }}</th>
                                    <th>{{ __('admin.questions.type') }}</th>
                                    <th>{{ __('admin.news.content') }}</th>
                                    <th>{{ __('admin.posts.status') }}</th>
                                    <th>{{ __('admin.users.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stories as $story)
                                <tr>
                                    <td>{{ $story->id }}</td>
                                    <td>
                                        @if($story->user)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm mr-1">
                                                <img src="{{ $story->user->profile_photo_url }}" alt="avatar">
                                            </div>
                                            <span>{{ $story->user->name }}</span>
                                        </div>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ strtoupper($story->type) }}</span>
                                    </td>
                                    <td>
                                        @if($story->type === 'image')
                                        <a href="{{ $story->media_url }}" target="_blank">
                                            <img src="{{ $story->media_url }}" width="50" class="img-thumbnail">
                                        </a>
                                        @elseif($story->type === 'video')
                                        <a href="{{ $story->media_url }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="la la-play"></i>
                                        </a>
                                        @else
                                        {{ Str::limit($story->content, 50) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($story->expires_at > now())
                                        <span class="badge badge-success">{{ __('admin.status.active') }}</span>
                                        <br><small>{{ $story->expires_at->diffForHumans() }}</small>
                                        @else
                                        <span class="badge badge-danger">{{ __('admin.status.inactive') ?? 'Expired' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="{{ $story->id }}">
                                            <i class="la la-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $story->id }}" action="{{ route('admin.stories.destroy', $story->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ __('admin.categories.no_records') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @if($stories->hasPages())
            <div class="card-footer">
                {{ $stories->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {
        $('.delete-btn').on('click', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: '{{ __("admin.messages.confirm_delete") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("admin.buttons.delete") }}',
                cancelButtonText: '{{ __("admin.buttons.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#delete-form-' + id).submit();
                }
            });
        });
    });
</script>
@endpush
@endsection
