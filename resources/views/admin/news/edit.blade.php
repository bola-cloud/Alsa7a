@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-4">
        <div class="content-header-left col-md-12 col-12 mb-2">
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">{{ __('admin.menu.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.news.index') }}">{{ __('admin.news.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.news.edit') }}</li>
                    </ol>
                </div>
            </div>
            <h3 class="content-header-title mb-0">{{ __('admin.news.edit') }}</h3>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('admin.news.edit') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <div class="form-group">
                                        <label>{{ __('admin.categories.name') }} ({{ $properties['native'] }}) <span class="text-danger">*</span></label>
                                        <input type="text" name="title[{{ $localeCode }}]" class="form-control" required
                                            value="{{ old('title.' . $localeCode, $news->{'title_' . $localeCode}) }}" placeholder="{{ __('admin.categories.name') }} ({{ $properties['native'] }})">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('admin.categories.description') }} ({{ $properties['native'] }})</label>
                                        <textarea name="content[{{ $localeCode }}]" class="form-control"
                                            rows="10" placeholder="{{ __('admin.categories.description') }} ({{ $properties['native'] }})">{{ old('content.' . $localeCode, $news->{'content_' . $localeCode}) }}</textarea>
                                    </div>
                                @endforeach
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ __('admin.news.sport') }}</label>
                                    <select name="sport_id" class="form-control">
                                        <option value="">{{ __('admin.categories.no') }}</option>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}" {{ $news->sport_id == $sport->id ? 'selected' : '' }}>
                                                {{ $sport->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <hr>

                                <div class="form-group">
                                    <label>{{ __('admin.news.image') }} (Main)</label>
                                    @if($news->featured_image)
                                        <div class="mb-2">
                                            <img src="{{ $news->featured_image }}" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @elseif($news->image && !str_contains($news->image, 'http'))
                                         <!-- Fallback for older image storage if needed -->
                                        <div class="mb-2">
                                            <img src="{{ asset($news->image) }}" class="img-thumbnail" style="max-height: 150px;">
                                        </div>
                                    @endif
                                    
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="image" id="newsImage">
                                        <label class="custom-file-label" for="newsImage">{{ __('admin.news.choose_file') }}</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('admin.news.gallery_images') }}</label>
                                    <!-- Existing Images -->
                                    @if($news->images->count() > 0)
                                        <div class="row mb-2" id="existing-gallery">
                                            @foreach($news->images as $img)
                                                <div class="col-md-4 col-6 mb-2 position-relative" id="existing-img-{{ $img->id }}">
                                                    <div class="card h-100 border p-0">
                                                        <a href="{{ $img->full_url }}" target="_blank">
                                                            <img src="{{ $img->full_url }}" class="card-img-top w-100 h-100"
                                                                style="object-fit: cover;">
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm position-absolute"
                                                            style="top: 0; right: 0; z-index: 10; border-radius: 0 0 0 4px;"
                                                            title="{{ __('admin.buttons.delete') }}"
                                                            onclick="markForDeletion({{ $img->id }})">
                                                            <i class="la la-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div id="deleted-images-container"></div>
                                    @endif

                                    <!-- New Images Upload -->
                                    <div class="custom-file mb-1">
                                        <input type="file" class="custom-file-input" id="gallery-upload" multiple
                                            accept="image/*">
                                        <label class="custom-file-label" for="gallery-upload">{{ __('admin.news.choose_images') }}</label>
                                    </div>
                                    <input type="file" name="images[]" id="real-gallery-input" multiple style="display: none;">

                                    <div id="gallery-preview" class="row mt-2"></div>
                                </div>

                                <hr>

                                <div class="form-group">
                                    <label>{{ __('admin.news.video') }}</label>
                                    @if($news->video)
                                        <div class="mb-2">
                                            <a href="{{ $news->video->full_url }}" target="_blank" class="btn btn-sm btn-info w-100">
                                                <i class="la la-play"></i> {{ __('admin.buttons.view') }} Video
                                            </a>
                                        </div>
                                    @endif
                                    <div class="custom-file mb-2">
                                        <input type="file" class="custom-file-input" name="video" id="newsVideo">
                                        <label class="custom-file-label" for="newsVideo">{{ __('admin.news.choose_video') }}</label>
                                    </div>
                                    <input type="url" name="video_url" class="form-control" placeholder="{{ __('admin.news.video_url') }}"
                                        value="{{ $news->video && !preg_match('#^news/videos#', $news->video->url) ? $news->video->url : '' }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions text-right mt-4">
                            <a href="{{ route('admin.news.index') }}" class="btn btn-warning mr-2">
                                <i class="la la-times"></i> {{ __('admin.buttons.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-check-circle"></i> {{ __('admin.buttons.update') }}
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
        function markForDeletion(id) {
            if (confirm('Are you sure you want to remove this image? (Will be deleted on Save)')) {
                // Remove from view
                document.getElementById('existing-img-' + id).remove();

                // Add hidden input
                const container = document.getElementById('deleted-images-container');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_images[]';
                input.value = id;
                container.appendChild(input);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // --- Gallery Logic for New Images ---
            const galleryUpload = document.getElementById('gallery-upload');
            const realInput = document.getElementById('real-gallery-input');
            const previewContainer = document.getElementById('gallery-preview');
            let dataTransfer = new DataTransfer();

            // Handle file selection
            galleryUpload.addEventListener('change', function () {
                const files = this.files;

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    // Add to dataTransfer
                    dataTransfer.items.add(file);

                    // Create preview element
                    const col = document.createElement('div');
                    col.className = 'col-md-3 col-4 mb-2 position-relative';

                    const card = document.createElement('div');
                    card.className = 'card h-100 border';

                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.className = 'card-img-top';
                    img.style.height = '100px';
                    img.style.objectFit = 'cover';

                    const btn = document.createElement('button');
                    btn.className = 'btn btn-danger btn-sm position-absolute';
                    btn.style.top = '0';
                    btn.style.right = '15px';
                    btn.style.zIndex = '10';
                    btn.innerHTML = '<i class="la la-trash"></i>';
                    btn.type = 'button';

                    // Delete functionality
                    btn.onclick = function () {
                        col.remove();

                        const newDataTransfer = new DataTransfer();
                        for (let j = 0; j < dataTransfer.files.length; j++) {
                            if (dataTransfer.files[j] !== file) {
                                newDataTransfer.items.add(dataTransfer.files[j]);
                            }
                        }
                        dataTransfer = newDataTransfer;
                        realInput.files = dataTransfer.files;
                    };

                    card.appendChild(img);
                    col.appendChild(card);
                    col.appendChild(btn);
                    previewContainer.appendChild(col);
                }

                // Sync with real input
                realInput.files = dataTransfer.files;

                // Reset the upload input
                this.value = '';
            });
        });
    </script>
@endpush