# Backend Task — Deep Linking Support for AlSaha (App Links + Universal Links)

**To:** Backend Engineer
**App:** الساحة | AlSaha
**Domain:** `https://alsaha.tech`

> Make the domain support the mobile app's **Android App Links** and **iOS Universal Links**. All real values are filled in below. The deliverables are in §8.

> **Why `/app/`?** All mobile links are namespaced under a `/app/` path prefix, so the app only intercepts `https://alsaha.tech/app/...` and never hijacks the rest of the website. Keep every deep-link route under `/app/`.

---

## §1 — Project values (all final)

| Variable | Value |
|---|---|
| Domain | `alsaha.tech` |
| App name | الساحة \| AlSaha |
| Android package | `com.alsaha.alsaha` |
| iOS bundle id | `com.alsaha.alsaha` |
| Apple Team ID | `X78PQ2MU22` |
| Apple App ID (full) | `X78PQ2MU22.com.alsaha.alsaha` |
| SHA-256 — Google Play App Signing | `4A:EC:6B:C0:C7:38:DB:74:BC:7F:70:2B:EA:83:F1:95:7A:88:BA:75:BD:C9:26:A6:8D:36:38:E8:2F:D9:4D:9A` |
| SHA-256 — upload keystore | `4D:90:DB:55:70:E7:57:08:03:9C:50:63:E7:22:D3:22:9D:EE:0B:B3:78:21:6C:12:88:F0:D8:F4:EB:3B:2B:66` |
| SHA-256 — debug keystore (testing only; remove before final production) | `CF:8E:7D:EF:6D:A9:E9:ED:3E:2F:60:29:88:93:89:AB:E2:50:52:C1:3B:E0:32:C0:DF:2E:0F:C9:5E:49:AD:5E` |
| Google Play link | `https://play.google.com/store/apps/details?id=com.alsaha.alsaha` |
| App Store link | `https://apps.apple.com/us/app/id6761012764` |
| App icon for fallback page | `<APP_ICON_URL>` *(supply a logo URL, e.g. https://alsaha.tech/img/app-icon.png)* |

**Deep-link URL shape:** `https://alsaha.tech/app/<type>/<id-or-slug>` (e.g. `/app/profile/123`, `/app/post/9`).

> The backend does **not** need the list of `type`s or a route per screen. The mobile app owns the routes and the in-app navigation. The backend only needs **one catch-all** for `/app/*` (see §3). The individual `type`s matter to the backend **only if** you want the fallback page to preview the specific content (§4b) — otherwise ignore them.

---

## §2 — Verification files (these make the OS trust the app)

Serve **over HTTPS, HTTP 200, `Content-Type: application/json`, NO redirects, public (no auth)**.

### 2a. Android — `https://alsaha.tech/.well-known/assetlinks.json`
```json
[
  {
    "relation": ["delegate_permission/common.handle_all_urls"],
    "target": {
      "namespace": "android_app",
      "package_name": "com.alsaha.alsaha",
      "sha256_cert_fingerprints": [
        "4A:EC:6B:C0:C7:38:DB:74:BC:7F:70:2B:EA:83:F1:95:7A:88:BA:75:BD:C9:26:A6:8D:36:38:E8:2F:D9:4D:9A",
        "4D:90:DB:55:70:E7:57:08:03:9C:50:63:E7:22:D3:22:9D:EE:0B:B3:78:21:6C:12:88:F0:D8:F4:EB:3B:2B:66",
        "CF:8E:7D:EF:6D:A9:E9:ED:3E:2F:60:29:88:93:89:AB:E2:50:52:C1:3B:E0:32:C0:DF:2E:0F:C9:5E:49:AD:5E"
      ]
    }
  }
]
```
> Fingerprints: ① Google Play App Signing (production from the Store), ② upload keystore (direct/sideload release), ③ debug (testing — safe to remove before final production launch).

### 2b. iOS — `https://alsaha.tech/.well-known/apple-app-site-association`
**Filename has NO `.json` extension.**
```json
{
  "applinks": {
    "apps": [],
    "details": [
      {
        "appIDs": ["X78PQ2MU22.com.alsaha.alsaha"],
        "components": [
          { "/": "/app/*", "comment": "All mobile app deep links live under /app/" }
        ]
      }
    ]
  }
}
```

### 2c. Server config checklist
- [ ] `/.well-known/` reachable publicly (not blocked by `.htaccess` / nginx / firewall)
- [ ] Both files return **HTTP 200** (no 301/302 redirect — verification fails on redirects)
- [ ] `Content-Type: application/json` on both (including the iOS file, despite no extension)
- [ ] No auth, no CORS block, valid HTTPS certificate

---

## §3 — Web routes under `/app/...`  (ONE catch-all — do NOT enumerate every route)

> **Important:** the backend does **not** need the list of individual app screens/paths. The mobile app defines and parses the routes itself, and handles all navigation **when the app is installed**. The backend's only job is a single **catch-all** that serves the fallback page for **anything** under `/app/`.

Add **one** wildcard route group with prefix `app`, isolated from the existing website:

```php
// Laravel — one group covers /app/profile/5, /app/post/9, /app/anything...
Route::prefix('app')->group(function () {
    Route::get('{any?}', fn () => view('app_fallback'))->where('any', '.*');
});
```
```js
// Express equivalent
app.get(['/app', '/app/*'], (req, res) => res.send(fallbackHtml));
```

Behaviour of every `/app/...` URL:
- **App installed** → the OS opens the app **before** the page loads (the page is never seen); the mobile app handles navigation.
- **App not installed / desktop** → this catch-all renders the fallback page (§4) with download buttons.

> So you only build **one** route (`/app/*`) and **one** page — not a route per screen. The same URL works everywhere: opens the app if installed, else shows the download page.

---

## §4 — Fallback page (open-in-app / download)

When the link is opened **without** the app (or on a computer), show a clean branded page with store-download buttons. *(Reference design: a centered white card with the app icon, app name, a short "install to open this content" message, a primary Google Play button, a secondary App Store button, and a footer note that the content opens automatically if the app is installed.)*

### 4a. Required elements
- App icon (`<APP_ICON_URL>`) + app name **الساحة | AlSaha**
- Short message: *"لفتح هذا المحتوى داخل التطبيق، يُرجى تثبيت تطبيق الساحة."*
- **Google Play** button → `https://play.google.com/store/apps/details?id=com.alsaha.alsaha`
- **App Store** button → `https://apps.apple.com/us/app/id6761012764`
- Footer note: *"إذا كان التطبيق مثبتًا لديك، سيُفتح المحتوى تلقائيًا."*
- RTL (Arabic): `dir="rtl"`

### 4b. Recommended
- **Platform detection:** emphasize Play on Android, App Store on iPhone/iPad, show both on desktop.
- **Content preview:** use the route `{id}`/`{slug}` to show the resource's title/image + Open Graph tags (nice link previews when shared).

### 4c. Sample HTML
```html
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>الساحة | AlSaha</title>
  <meta property="og:title" content="{{ CONTENT_TITLE | default: 'الساحة | AlSaha' }}" />
  <meta property="og:image" content="{{ CONTENT_IMAGE | default: '<APP_ICON_URL>' }}" />
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
    <img class="icon" src="<APP_ICON_URL>" alt="الساحة | AlSaha" />
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
```

---

## §5 — Slug / id consistency (critical)

- [ ] Slugs are URL-safe (ASCII, no spaces, no Arabic characters — use hyphens)
- [ ] Slugs/ids are unique and **stable** (never change after creation — they are permanent links)
- [ ] The slug/id in `/app/<type>/<slug>` **exactly matches** the field the relevant API endpoint returns

---

## §6 — OneSignal payload alignment (only if the app uses push)

Deep links and push notifications share the same `type` keys. For a push that should open a specific screen, set **Additional Data**:
```json
{ "type": "<type>", "id": "<id-or-slug>" }
```
Use the exact `type` values agreed with the mobile team. Unknown types fall back to the app's home screen.

---

## §7 — Verification & testing (after hosting)

- **Android (Google checker):**
  `https://digitalassetlinks.googleapis.com/v1/statements:list?source.web.site=https://alsaha.tech&relation=delegate_permission/common.handle_all_urls`
  → must return the statement with `com.alsaha.alsaha` + the fingerprints.
- **iOS:** open `https://alsaha.tech/.well-known/apple-app-site-association` → returns the JSON directly (200, `application/json`, no redirect).
- **Routes:** `https://alsaha.tech/app/<type>/<id>` → returns 200 and the fallback page in a desktop browser.
- Mobile team then tests on-device (tapping the link opens the app).

---

## §8 — Deliverables checklist

- [ ] `https://alsaha.tech/.well-known/assetlinks.json` hosted (package `com.alsaha.alsaha` + the 3 SHA-256 fingerprints, no redirects)
- [ ] `https://alsaha.tech/.well-known/apple-app-site-association` hosted (`X78PQ2MU22.com.alsaha.alsaha`, no `.json` extension, no redirects)
- [ ] `/app/...` route group serving the fallback page
- [ ] Fallback page: icon, name, message, Play + App Store buttons, footer note, RTL, platform detection
- [ ] (optional) Content preview + Open Graph tags using `{id}`/`{slug}`
- [ ] Slugs/ids URL-safe, unique, stable, matching API responses
- [ ] (if push) OneSignal Additional Data uses the agreed `type`/`id` keys
- [ ] Verified via Google checker + direct AASA fetch

---

### Notes for the mobile team (action items)
- Provide `<APP_ICON_URL>` for the fallback page (a hosted logo image).
- Confirm the final list of deep-link `type`s and which screen each opens.
- Remove the **debug** SHA-256 from `assetlinks.json` before the final production launch.
