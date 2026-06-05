@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h1>{{ __('admin.users.title') }}</h1>

        <div class="mb-3">
             <div class="row">
                 <div class="col-md-6 mb-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-default mr-1">{{ __('admin.users.all') }}</a>
                    <a href="{{ route('admin.users.index', ['pending_approval' => 1]) }}" class="btn btn-outline-warning mr-1">{{ __('admin.users.pending_approval') }}</a>
                    <a href="{{ route('admin.users.index', ['pending_verification' => 1]) }}" class="btn btn-outline-info">{{ __('admin.users.doc_verification') }}</a>
                 </div>
                 <div class="col-md-6 text-right">
                     <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="ft-plus"></i> {{ __('admin.users.add_user') }}</a>
                 </div>
             </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body p-3">
                <form action="{{ route('admin.users.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="text-muted small mb-1">{{ __('admin.buttons.search') }}</label>
                            <div class="position-relative">
                                <input type="text" name="search" class="form-control pl-4" placeholder="{{ __('admin.users.search_placeholder') }}" value="{{ request('search') }}">
                                <button type="submit" class="position-absolute border-0 bg-transparent p-0" style="top: 8px; left: 10px; color: #b0afb5; cursor: pointer;">
                                    <i class="la la-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="text-muted small mb-1">{{ __('admin.users.role_category') }}</label>
                            <select name="category_id" class="form-control">
                                <option value="">{{ __('admin.users.all_roles') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="text-muted small mb-1">{{ __('admin.users.subscription_status') }}</label>
                            <select name="subscription_status" class="form-control">
                                <option value="">{{ __('admin.users.all_subscriptions') }}</option>
                                <option value="subscribed" {{ request('subscription_status') == 'subscribed' ? 'selected' : '' }}>{{ __('admin.users.subscribed') }}</option>
                                <option value="unsubscribed" {{ request('subscription_status') == 'unsubscribed' ? 'selected' : '' }}>{{ __('admin.users.not_subscribed') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <label class="text-muted small mb-1">{{ __('admin.users.sort_by') }}</label>
                            <select name="sort" class="form-control">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>{{ __('admin.users.sort_latest') }}</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('admin.users.sort_oldest') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="la la-filter"></i> {{ __('admin.users.filter') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <form action="{{ route('admin.users.bulk') }}" method="POST" id="bulk-actions-form">
            @csrf
            <div class="card mb-3">
                <div class="card-body p-2 flex items-center">
                    <div class="d-flex align-items-center">
                        <select name="action" class="form-control form-control-sm mr-2" style="width: auto;" required>
                            <option value="">{{ __('admin.users.bulk_actions') ?? 'Bulk Actions' }}</option>
                            <option value="delete">{{ __('admin.buttons.delete') }}</option>
                            <option value="approve">{{ __('admin.users.approve') }}</option>
                            <option value="block">{{ __('admin.users.block') }}</option>
                            <option value="unblock">{{ __('admin.users.unblock') }}</option>
                            <option value="activate_subscription">{{ __('admin.users.activate_subscription') }}</option>
                            <option value="cancel_subscription">{{ __('admin.users.cancel_subscription') }}</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-dark">
                            {{ __('admin.buttons.apply') ?? 'Apply' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body pl-0 pr-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="select-all">
                                    </th>
                                    <th>{{ __('admin.users.id') }}</th>
                                    <th>{{ __('admin.users.name') }}</th>
                                    <th>{{ __('admin.users.role') }}</th>
                                    <th>{{ __('admin.users.status') }}</th>
                                    <th>{{ __('admin.users.verification') }}</th>
                                    <th>{{ __('admin.users.join_date') }}</th>
                                    <th>{{ __('admin.users.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            @if($user->email !== 'admin@alsa7a.com')
                                                <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="user-checkbox">
                                            @endif
                                        </td>
                                        <td>{{ $user->id }}</td>
                                        <td>
                                            <div>{{ $user->name }}</div>
                                            <small class="text-muted">{{ $user->email }} / {{ $user->phone }}</small>
                                        </td>
                                        <td>{{ $user->category->name ?? 'User' }}</td>
                                        <td>
                                            @if($user->is_approved)
                                                <span class="badge badge-success">{{ __('admin.users.approved') }}</span>
                                            @else
                                                <span class="badge badge-warning">{{ __('admin.users.pending') }}</span>
                                            @endif

                                            @if($user->is_blocked)
                                                <span class="badge badge-danger">{{ __('admin.users.block') }}</span>
                                            @endif

                                            @if($user->isSubscribed())
                                                <span class="badge badge-info">{{ __('admin.users.subscribed') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ __('admin.users.not_subscribed') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->verification_status === 'approved')
                                                <span class="badge badge-success">{{ __('admin.users.verified') }}</span>
                                            @elseif($user->verification_status === 'pending')
                                                <span class="badge badge-warning">{{ __('admin.users.docs_pending') }}</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($user->verification_status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user->id) }}"
                                                class="btn btn-sm btn-info" title="{{ __('admin.buttons.view') }}"><i class="la la-eye"></i></a>
                                            
                                            @if($user->email !== 'admin@alsa7a.com')
                                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary" title="{{ __('admin.buttons.edit') }}"><i class="la la-edit"></i></a>
                                                
                                                <form action="{{ route('admin.users.toggle_block', $user->id) }}" method="POST"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $user->is_blocked ? 'btn-success' : 'btn-warning' }}" 
                                                        title="{{ $user->is_blocked ? __('admin.users.unblock') : __('admin.users.block') }}">
                                                        <i class="la {{ $user->is_blocked ? 'la-unlock' : 'la-ban' }}"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                                    style="display:inline-block;" onsubmit="return confirm('{{ __('admin.messages.confirm_delete') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="{{ __('admin.buttons.delete') }}"><i class="la la-trash"></i></button>
                                                </form>
                                            @endif

                                            @if(!$user->is_approved)
                                                <form action="{{ route('admin.users.approve', $user->id) }}" method="POST"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="{{ __('admin.users.approve_access') }}"><i class="la la-check"></i></button>
                                                </form>
                                            @endif

                                            @if($user->email !== 'admin@alsa7a.com')
                                                @if(!$user->isSubscribed())
                                                    <form action="{{ route('admin.users.activate_subscription', $user->id) }}" method="POST"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('admin.users.activate_subscription') }}"><i class="la la-rocket"></i></button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.users.cancel_subscription', $user->id) }}" method="POST"
                                                        style="display:inline-block;" onsubmit="return confirm('{{ __('admin.users.confirm_cancel_subscription') }}');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('admin.users.cancel_subscription') }}"><i class="la la-close"></i></button>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </form>

        <script>
            document.getElementById('select-all').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.user-checkbox');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            document.getElementById('bulk-actions-form').addEventListener('submit', function(e) {
                const selected = document.querySelectorAll('.user-checkbox:checked');
                if (selected.length === 0) {
                    e.preventDefault();
                    alert('{{ __('admin.messages.select_at_least_one') ?? 'Please select at least one item.' }}');
                } else {
                    const action = this.querySelector('select[name="action"]').value;
                    if (action === 'delete') {
                        if (!confirm('{{ __('admin.messages.confirm_delete_selected') ?? 'Are you sure you want to delete selected items?' }}')) {
                            e.preventDefault();
                        }
                    }
                }
            });
        </script>
    </div>
@endsection