# توثيق تحديثات الباك إند - تطبيق الساحة (الإصدار الجديد)

هذا الملف يحتوي على التوثيق الخاص بالمسارات (Endpoints) والميزات الجديدة التي تمت إضافتها للتطبيق.

---

## 1. الأخبار (News API)
تم إضافة مسارات جديدة لجلب الأخبار، مع دعم الترقيم (Pagination) وحالة الإعجاب.

**أ. جلب قائمة الأخبار:**
- **الطريقة:** `GET`
- **المسار:** `/api/v1/news`
- **البارامترات (اختياري):** `sport_id` للفلترة برياضة معينة.
- **مثال للرد (Response):**
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "title": "عنوان الخبر",
                "content": "تفاصيل الخبر",
                "image": "رابط الصورة",
                "sport_id": 2,
                "likes_count": 15,
                "is_liked": false,
                "created_at": "2026-06-15T12:00:00.000000Z"
            }
        ]
    },
    "message": "News retrieved successfully"
}
```

**ب. جلب خبر محدد:**
- **الطريقة:** `GET`
- **المسار:** `/api/v1/news/{id}`

---

## 2. قائمة الإعجابات (Post Likers)
يمكنك الآن جلب قائمة بالمستخدمين الذين قاموا بالإعجاب ببوست معين (مع الترقيم).

- **الطريقة:** `GET`
- **المسار:** `/api/v1/posts/{id}/likes`
- **مثال للرد:**
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 5,
                "name": "أحمد",
                "avatar": "...",
                "profile_title": "مهاجم"
            }
        ]
    },
    "message": "Post likers retrieved successfully"
}
```

---

## 3. زيارات البروفايل (Profile Visitors)
يتم تسجيل زيارات الملف الشخصي تلقائياً عند استدعاء مسار `GET /api/v1/users/{id}/profile`. 
لعرض قائمة من زار ملفي:

- **الطريقة:** `GET`
- **المسار:** `/api/v1/profile/visitors`
- **التوثيق (Auth):** مطلوب (Bearer Token)
- **مثال للرد:**
```json
{
    "status": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 10,
                "visitor_id": 4,
                "visited_id": 1,
                "updated_at": "2026-06-15T14:30:00.000000Z",
                "visitor": {
                    "id": 4,
                    "name": "محمود",
                    "avatar": "...",
                    "phone": "01000000000"
                }
            }
        ]
    },
    "message": "Profile visitors retrieved successfully"
}
```

---

## 4. تسجيل دخول ولي الأمر (Parent View-Only Login)
حساب ولي الأمر له صلاحية "المشاهدة فقط". أي محاولة لإضافة إعجاب، تعليق، أو بوست ستُقابل بالرفض `403 Forbidden`.

- **الطريقة:** `POST`
- **المسار:** `/api/v1/auth/parent-login`
- **البارامترات:** `parent_code` (String)
- **مثال للرد:**
```json
{
    "user": {
        "id": 1,
        "name": "كابتن أحمد",
        "parent_code": "P-123456"
    },
    "token": "1|abcdef...",
    "is_parent_view": true
}
```

---

## 5. المنشن في البوستات والكوميونتي (Mentions)
يمكنك الآن الإشارة لمستخدمين آخرين عند إنشاء بوست (بروفايل أو كوميونتي). 
المسارات الخاصة بإنشاء البوستات لم تتغير في الـ URL، ولكن تم إضافة حقل جديد في الـ Request وهو `mentions`.

**في الـ Request الخاص بإنشاء بوست:** `POST /api/v1/posts` أو `POST /api/v1/community/posts`
- أضف مصفوفة `mentions` تحتوي على معرّفات (IDs) المستخدمين.
```json
{
    "content": "مباراة رائعة اليوم مع الشباب!",
    "mentions": [4, 7, 9]
}
```

**في الـ Response عند جلب أي بوست (يتم إرجاع مصفوفة mentions مدمجة):**
```json
{
    "id": 100,
    "content": "مباراة رائعة اليوم مع الشباب!",
    "user_id": 1,
    "mentions": [
        {
            "id": 4,
            "name": "محمد",
            "avatar": "..."
        },
        {
            "id": 7,
            "name": "يوسف",
            "avatar": "..."
        }
    ]
}
```
*ملاحظة: سيقوم السيرفر تلقائياً بإرسال إشعارات (Push Notifications) للأشخاص الذين تم عمل منشن لهم.*

---

## 6. الديب لينك (Deep Linking)
تم تجهيز الـ Backend ليدعم الديب لينك للروابط العالمية.
- تم رفع ملفات `assetlinks.json` للأندرويد و `apple-app-site-association` للآيفون على المسار `/.well-known/`.
- **المسار الموحد للتطبيق:** أي رابط يبدأ بـ `https://alsaha.tech/app/` سيتم توجيهه تلقائياً لفتح التطبيق إذا كان مثبتاً.
- إذا لم يكن التطبيق مثبتاً (أو عند فتحه من متصفح كمبيوتر)، ستظهر شريحة (Fallback Page) جذابة تحتوي على أيقونة التطبيق وأزرار التحميل من المتاجر.
- **مثال لرابط ديب لينك صحيح:** `https://alsaha.tech/app/post/123`
- *لا يحتاج الموبايل لتعريف كل مسار في الباك إند، الموبايل يقرأ الرابط من الـ Intent ويعالجه داخلياً.*

---

## 7. رفع صور متعددة للبوست (Multiple Images Carousel)
تم دعم رفع أكثر من صورة للبوست الواحد (في البروفايل أو الكوميونتي) مع الحفاظ على التوافقية مع التطبيق القديم (Backward Compatibility).

**طريقة الرفع:** `POST /api/v1/posts` أو `POST /api/v1/community/posts`
بدلاً من إرسال صورة واحدة في الحقل `image`، يمكنك الآن إرسال مصفوفة من الصور في الحقل `images[]`.
*إذا أرسلت `images[]` سيتم اعتبار أول صورة هي الصورة الأساسية للبوست وسيتم حفظها في `image` (للإصدارات القديمة)، وسيتم حفظ جميع الصور في مصفوفة `images` المرفقة.*

**مثال للرد (Response):**
```json
{
    "id": 105,
    "content": "صور من تدريب اليوم",
    "image": "https://alsaha.tech/storage/posts/img1.jpg", 
    "images": [
        {
            "id": 1,
            "post_id": 105,
            "url": "https://alsaha.tech/storage/posts/img1.jpg"
        },
        {
            "id": 2,
            "post_id": 105,
            "url": "https://alsaha.tech/storage/posts/img2.jpg"
        }
    ]
}
```
