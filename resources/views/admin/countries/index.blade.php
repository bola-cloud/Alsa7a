@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('admin.countries.index') }}</h4>
                <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li>
                            <a href="{{ route('admin.countries.create') }}" class="btn btn-primary btn-sm rounded-pill">
                                <i class="ft-plus white"></i> {{ __('admin.countries.create') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-content collapse show">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('admin.countries.flag') }}</th>
                                    <th>{{ __('admin.countries.name_en') }}</th>
                                    <th>{{ __('admin.countries.name_ar') }}</th>
                                    <th>{{ __('admin.countries.code') }}</th>
                                    <th>{{ __('admin.countries.is_active') }}</th>
                                    <th>{{ __('admin.buttons.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($countries as $index => $country)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($country->flag)
                                            <img src="{{ $country->flag_url }}" alt="Flag" style="width: 40px; height: 25px; object-fit: cover;">
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $country->name_en }}</td>
                                    <td>{{ $country->name_ar }}</td>
                                    <td>{{ $country->code }}</td>
                                    <td>
                                        @if($country->is_active)
                                            <span class="badge badge-success">{{ __('admin.buttons.yes') }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ __('admin.buttons.no') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.countries.edit', $country->id) }}" class="btn btn-outline-info btn-sm">
                                                <i class="ft-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.countries.destroy', $country->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('admin.messages.confirm_delete') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="ft-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">{{ __('admin.messages.no_data') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-2">
                        {{ $countries->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
