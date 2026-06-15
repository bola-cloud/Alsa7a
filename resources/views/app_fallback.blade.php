<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>الساحة | AlSaha</title>
  <meta property="og:title" content="الساحة | AlSaha" />
  <meta property="og:image" content="https://alsaha.tech/favicon.ico" />
  <style>
    body{margin:0;font-family:system-ui,'Cairo',sans-serif;background:#f1f3f4;
         display:flex;min-height:100vh;align-items:center;justify-content:center}
    .card{background:#fff;border-radius:20px;padding:32px;max-width:420px;width:90%;
          text-align:center;box-shadow:0 10px 40px rgba(0,0,0,.08)}
    .icon{width:84px;height:84px;border-radius:18px;object-fit:cover;margin-bottom:16px}
    h1{font-size:20px;margin:8px 0}
    p{color:#5f6368;font-size:14px;line-height:1.7}
    .btn{display:block;padding:14px;border-radius:12px;margin:10px 0;text-decoration:none;font-weight:700}
    .btn-primary{background:#1a8f5a;color:#fff}
    .btn-secondary{background:#eef3f0;color:#1a8f5a}
    .note{color:#9aa0a6;font-size:12px;margin-top:14px}
  </style>
</head>
<body>
  <div class="card">
    <!-- Replace with actual logo URL -->
    <img class="icon" src="https://alsaha.tech/favicon.ico" alt="الساحة | AlSaha" />
    <h1>الساحة | AlSaha</h1>
    <p>لفتح هذا المحتوى داخل التطبيق، يُرجى تثبيت تطبيق الساحة.</p>
    <a class="btn btn-primary"   id="android" href="https://play.google.com/store/apps/details?id=com.alsaha.alsaha">تحميل التطبيق — Google Play</a>
    <a class="btn btn-secondary" id="ios"     href="https://apps.apple.com/us/app/id6761012764">تحميل التطبيق — App Store</a>
    <p class="note">إذا كان التطبيق مثبتًا لديك، سيُفتح المحتوى تلقائيًا.</p>
  </div>
  <script>
    var ua = navigator.userAgent || '';
    if (/android/i.test(ua)) { document.getElementById('ios').style.display='none'; }
    else if (/iphone|ipad|ipod/i.test(ua)) { document.getElementById('android').style.display='none'; }
  </script>
</body>
</html>
