<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ setting('site_name', 'Alsa7a') }}</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    @php
        $iconPath = setting('site_icon');
        if ($iconPath && !filter_var($iconPath, FILTER_VALIDATE_URL) && !str_contains($iconPath, 'app-assets')) {
            $iconUrl = asset('storage/' . $iconPath);
        } else {
            $iconUrl = asset($iconPath ?: 'app-assets/images/logo.jpeg');
        }
    @endphp
    <link rel="apple-touch-icon" href="{{ asset('app-assets/images/ico/apple-icon-120.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ $iconUrl }}">
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
        }
        .navbar {
            background: linear-gradient(90deg, #34d399 0%, #0ea5e9 100%);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand img {
            height: 48px;
            border-radius: 8px;
            margin-inline-end: 15px;
            background: white;
            padding: 2px;
        }
        .navbar-brand span {
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
        }
        .legal-card {
            margin-top: 3rem;
            margin-bottom: 3rem;
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid #f1f5f9;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .card-header h1 {
            font-weight: 700;
            color: #1e293b;
            margin: 0;
            font-size: 2rem;
        }
        .card-body {
            background-color: white;
            padding: 3rem 2.5rem;
        }
        .legal-content {
            font-size: 1.125rem;
            color: #475569;
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
        }
        .legal-content h2, .legal-content h3, .legal-content h4 {
            color: #1e293b;
            font-weight: 700;
            margin-top: 2.5rem;
            margin-bottom: 1.25rem;
        }
        .legal-content p {
            margin-bottom: 1.5rem;
        }
        .footer {
            text-align: center;
            padding: 2rem 0;
            color: #94a3b8;
            font-size: 0.875rem;
        }
        @media (max-width: 768px) {
            .card-body { padding: 2rem 1.5rem; }
            .card-header h1 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand d-flex align-items-center" href="#">
                @php
                    $logoPath = setting('site_logo');
                    // If stored via SettingController, it might be 'settings/abc.jpg'
                    // If it doesn't start with http and doesn't contain app-assets, it's likely in storage
                    if ($logoPath && !filter_var($logoPath, FILTER_VALIDATE_URL) && !str_contains($logoPath, 'app-assets')) {
                        $logoUrl = asset('storage/' . $logoPath);
                    } else {
                        $logoUrl = asset($logoPath ?: 'app-assets/images/logo.jpeg');
                    }
                @endphp
                <img src="{{ $logoUrl }}" alt="Logo">
                <span>{{ setting('site_name', 'Alsa7a') }}</span>
            </a>
            <div class="d-flex align-items-center">
                <a href="{{ App::getLocale() == 'ar' ? LaravelLocalization::getLocalizedURL('en') : LaravelLocalization::getLocalizedURL('ar') }}" 
                   class="btn btn-outline-light rounded-pill px-4 btn-sm">
                    <span class="text-uppercase">{{ App::getLocale() == 'ar' ? 'English' : 'العربية' }}</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="container contents-area">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-md-11">
                <div class="card legal-card">
                    <div class="card-header">
                        <h1>{{ $title }}</h1>
                        <div class="header-decoration"></div>
                    </div>
                    <div class="card-body">
                        <div class="legal-content">
                            {!! $content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer container pb-5">
        &copy; {{ date('Y') }} {{ setting('site_name', 'Alsa7a') }}. {{ __('All rights reserved.') }}
    </div>

</body>
</html>
