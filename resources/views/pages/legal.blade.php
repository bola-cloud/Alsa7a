@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-primary text-white p-4">
                    <h1 class="h3 mb-0 text-center">{{ $title }}</h1>
                </div>
                <div class="card-body p-5">
                    <div class="legal-content">
                        {!! $content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .legal-content {
        line-height: 1.8;
        color: #333;
        font-size: 1.1rem;
    }
    .legal-content h2, .legal-content h3, .legal-content h4 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #000;
        font-weight: 700;
    }
    .legal-content p {
        margin-bottom: 1.5rem;
    }
    .legal-content ul, .legal-content ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }
    [dir="rtl"] .legal-content {
        text-align: right;
    }
    [dir="rtl"] .legal-content ul, [dir="rtl"] .legal-content ol {
        padding-left: 0;
        padding-right: 1.5rem;
    }
</style>
@endsection
