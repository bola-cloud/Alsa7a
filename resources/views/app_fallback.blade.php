<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ $title ?? 'الساحة | AlSaha' }}</title>

  {{-- Open Graph (for link previews when shared) --}}
  <meta property="og:title"       content="{{ $title ?? 'الساحة | AlSaha' }}" />
  <meta property="og:description" content="{{ $description ?? 'تطبيق الساحة — المنصة الرياضية الأولى' }}" />
  <meta property="og:image"       content="{{ $image ?? 'https://alsaha.tech/app-assets/images/logo.jpeg' }}" />
  <meta property="og:url"         content="{{ url()->current() }}" />
  <meta property="og:type"        content="website" />

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

  <script>
    (function () {
      var ua = navigator.userAgent || navigator.vendor || window.opera || '';
      var isAndroid = /android/i.test(ua);
      var isIOS     = /iphone|ipad|ipod/i.test(ua) && !window.MSStream;

      if (isAndroid || isIOS) {
        var storeUrl = isAndroid
          ? 'https://play.google.com/store/apps/details?id=com.alsaha.alsaha'
          : 'https://apps.apple.com/us/app/id6761012764';
        
        // Redirect immediately to the store to avoid custom scheme warning popups
        window.location.replace(storeUrl);
      }
    })();
  </script>

  <style>
    *, *::before, *::after { box-sizing: border-box; }

    body {
      margin: 0;
      font-family: 'Cairo', system-ui, sans-serif;
      background: linear-gradient(135deg, #0d2137 0%, #1a4060 50%, #0d2137 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .card {
      background: #fff;
      border-radius: 24px;
      padding: 40px 32px;
      max-width: 420px;
      width: 100%;
      text-align: center;
      box-shadow: 0 20px 60px rgba(0,0,0,.3);
    }

    .icon-wrap {
      margin-bottom: 20px;
    }
    .icon {
      width: 90px;
      height: 90px;
      border-radius: 22px;
      object-fit: cover;
      box-shadow: 0 8px 24px rgba(26,143,90,.25);
    }
    .icon-fallback {
      width: 90px;
      height: 90px;
      border-radius: 22px;
      background: linear-gradient(135deg, #1a8f5a, #0d6640);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 42px;
      color: #fff;
    }

    h1 {
      font-size: 22px;
      font-weight: 900;
      margin: 0 0 10px 0;
      color: #0d2137;
    }

    .subtitle {
      color: #5f6368;
      font-size: 14px;
      line-height: 1.7;
      margin: 0 0 28px 0;
    }

    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 14px 20px;
      border-radius: 14px;
      margin: 10px 0;
      text-decoration: none;
      font-weight: 700;
      font-size: 15px;
      font-family: 'Cairo', sans-serif;
      transition: transform .15s, box-shadow .15s;
    }
    .btn:active { transform: scale(.97); }

    .btn-play {
      background: #1a8f5a;
      color: #fff;
      box-shadow: 0 4px 16px rgba(26,143,90,.35);
    }
    .btn-play:hover { box-shadow: 0 6px 20px rgba(26,143,90,.5); transform: translateY(-1px); }

    .btn-appstore {
      background: #000;
      color: #fff;
      box-shadow: 0 4px 16px rgba(0,0,0,.25);
    }
    .btn-appstore:hover { box-shadow: 0 6px 20px rgba(0,0,0,.4); transform: translateY(-1px); }

    .btn svg { flex-shrink: 0; }

    .note {
      color: #9aa0a6;
      font-size: 12px;
      margin-top: 16px;
      line-height: 1.6;
    }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon-wrap">
      <img class="icon"
           src="{{ $image ?? 'https://alsaha.tech/app-assets/images/logo.jpeg' }}"
           alt="الساحة"
           onerror="this.style.display='none';document.getElementById('icon-fallback').style.display='inline-flex';" />
      <span class="icon-fallback" id="icon-fallback" style="display:none;">⚽</span>
    </div>

    <h1>{{ $title ?? 'الساحة | AlSaha' }}</h1>
    <p class="subtitle">{{ $description ?? 'تطبيق الساحة — المنصة الرياضية الأولى' }}</p>

    <div class="platform-desktop">
      <a class="btn btn-play" href="https://play.google.com/store/apps/details?id=com.alsaha.alsaha">
        <svg width="18" height="18" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path fill="white" d="M4 4l20 20L4 44V4zm32 11.2L12.8 4 36 16.8zM36 31.2L12.8 44 36 31.2zm2-3.8L44 24l-6-3.4v6.8z"/></svg>
        Google Play — Android
      </a>
      <a class="btn btn-appstore" href="https://apps.apple.com/us/app/id6761012764">
        <svg width="18" height="20" viewBox="0 0 814 1000" xmlns="http://www.w3.org/2000/svg">
          <path d="M788.1 340.9c-5.8 4.5-108.2 62.2-108.2 190.5 0 148.4 130.3 200.9 134.2 202.2-.6 3.2-20.7 71.9-68.7 141.9-42.8 61.6-87.5 123.1-155.5 123.1s-85.5-39.5-164-39.5c-76 0-103.7 40.8-165.9 40.8s-105-57.8-155.5-127.4C46 790.7 0 663 0 541.8c0-207.1 134.6-316.6 267-316.6 70.5 0 129.2 46.4 173.7 46.4 42.8 0 109.6-49.5 189.1-49.5 30.3 0 108.2 2.6 168.9 80.3zm-139.3-126.3c-28.6 33.8-81.3 60.4-134 60.4-6.5 0-13-.6-19.5-1.9 1.3-53.9 23.4-110.6 60.4-148.9 37-38.2 95.2-65 152.9-67.3-.6 57.2-19.5 109.9-59.8 157.7z" fill="white"/>
        </svg>
        App Store — iPhone / iPad
      </a>
    </div>

    <p class="note">إذا كان التطبيق مثبتًا لديك، سيُفتح المحتوى تلقائيًا.</p>
  </div>
</body>
</html>
