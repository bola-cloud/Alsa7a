@extends('layouts.admin')

@section('content')
    <div class="content-header row mb-2 p-1 rounded" style="background-color: #f1f2f6;">
        <div class="content-header-left col-md-6 col-12">
            <h3 class="content-header-title">{{ __('admin.news.create') }}</h3>
            <div class="row breadcrumbs-top">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb bg-transparent mb-0 pl-0">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.news.index') }}">{{ __('admin.news.title') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('admin.news.create') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-content collapse show">
            <div class="card-body">
                <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                <div class="form-group">
                                    <label>{{ __('admin.categories.name') }} ({{ $properties['native'] }})</label>
                                    <input type="text" name="title[{{ $localeCode }}]" class="form-control" required
                                        value="{{ old('title.' . $localeCode) }}">
                                </div>
                                <div class="form-group">
                                    <label>{{ __('admin.categories.description') }} ({{ $properties['native'] }})</label>
                                    <textarea name="content[{{ $localeCode }}]" class="form-control"
                                        rows="5">{{ old('content.' . $localeCode) }}</textarea>
                                </div>
                            @endforeach
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __('admin.sports.title') }}</label>
                                <select name="sport_id" class="form-control">
                                    <option value="">{{ __('admin.categories.no') }}</option>
                                    @foreach($sports as $sport)
                                        <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ __('admin.categories.image') }} (Main)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="image" id="newsImage">
                                    <label class="custom-file-label" for="newsImage">Choose file</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Gallery Images</label>
                                <div class="custom-file mb-1">
                                    <input type="file" class="custom-file-input" id="gallery-upload" multiple
                                        accept="image/*">
                                    <label class="custom-file-label" for="gallery-upload">Choose images...</label>
                                </div>
                                <input type="file" name="images[]" id="real-gallery-input" multiple style="display: none;">

                                <div id="gallery-preview" class="row mt-2"></div>
                            </div>
                            <div class="form-group">
                                <label>Video</label>
                                <div class="custom-file mb-1">
                                    <input type="file" class="custom-file-input" name="video" id="newsVideo">
                                    <label class="custom-file-label" for="newsVideo">Choose video</label>
                                </div>
                                <input type="url" name="video_url" class="form-control" placeholder="OR Video URL">
                            </div>
                        </div>
                    </div>
                    <div class="form-actions text-right">
                        <a href="{{ route('admin.news.index') }}" class="btn btn-warning mr-1">
                            <i class="ft-x"></i> {{ __('admin.buttons.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ft-check"></i> {{ __('admin.buttons.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // --- Gallery Logic ---
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
                        // Rebuild loop excluding the deleted file
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