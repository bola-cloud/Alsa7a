@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2">
        <div class="content-header-left col-md-6 col-12 mb-2">
            <h3 class="content-header-title">{{ __('admin.events.edit') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.events.index') }}">{{ __('admin.events.index') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.events.edit') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.events.update', $event->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <div class="form-group">
                                        <label>{{ __('admin.events.title') }} ({{ $properties['native'] }})</label>
                                        <input type="text" name="title[{{ $localeCode }}]" class="form-control" required
                                            value="{{ old('title.' . $localeCode, $event->{'title_' . $localeCode}) }}">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('admin.categories.description') }} ({{ $properties['native'] }})</label>
                                        <textarea name="description[{{ $localeCode }}]" class="form-control"
                                            rows="3">{{ old('description.' . $localeCode, $event->{'description_' . $localeCode}) }}</textarea>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('admin.sports.index') }}</label>
                                    <select name="sport_id" class="form-control select2">
                                        <option value="">{{ __('admin.categories.no') }}</option>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}" {{ $event->sport_id == $sport->id ? 'selected' : '' }}>{{ $sport->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.events.date') }} (Start)</label>
                                            <input type="datetime-local" name="start_at" class="form-control" required
                                                value="{{ old('start_at', $event->start_at) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.events.date') }} (End)</label>
                                            <input type="datetime-local" name="end_at" class="form-control"
                                                value="{{ old('end_at', $event->end_at) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.events.venue') }}</label>
                                            <input type="text" name="venue" class="form-control"
                                                value="{{ old('venue', $event->venue) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.events.price') }}</label>
                                            <input type="number" step="0.01" name="price" class="form-control"
                                                value="{{ old('price', $event->price) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.events.capacity') }}</label>
                                            <input type="number" name="capacity" class="form-control"
                                                value="{{ old('capacity', $event->capacity) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ __('admin.events.status') }}</label>
                                            <select name="status" class="form-control">
                                                <option value="pending" {{ $event->status == 'pending' ? 'selected' : '' }}>{{ __('admin.status.pending') }}</option>
                                                <option value="approved" {{ $event->status == 'approved' ? 'selected' : '' }}>{{ __('admin.status.approved') }}</option>
                                                <option value="rejected" {{ $event->status == 'rejected' ? 'selected' : '' }}>{{ __('admin.status.rejected') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Featured Image</label>
                                    @if($event->featured_image)
                                        <div class="mb-2">
                                            <img src="{{ $event->featured_image }}" style="height: 60px" class="img-thumbnail">
                                        </div>
                                    @endif
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="featured_image" id="eventImage">
                                        <label class="custom-file-label" for="eventImage">Choose file</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions text-right">
                            <a href="{{ route('admin.events.index') }}" class="btn btn-warning mr-1">
                                <i class="la la-remove"></i> {{ __('admin.buttons.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-check"></i> {{ __('admin.buttons.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $('.custom-file-input').on('change', function () {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
@endpush