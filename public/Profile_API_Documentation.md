# 📄 توثيق كامل لمنظومة الـ API للملف الشخصي والاهتمامات والتنبيهات (Profile, Interests & Notifications API)

تم تنظيم هذا المستند لمساعدتك ومساعدة مطور تطبيق الموبايل (Flutter/Mobile developer) في فهم جميع الروابط (Endpoints) بالتفصيل مع أمثلة دقيقة للمدخلات والمخرجات وهيكل البيانات.

---

## 🔒 المصادقة والترويس (Headers & Authentication)
جميع الروابط المحمية (Protected Routes) تتطلب إرسال الـ `Token` المسترجع عند تسجيل الدخول في الترويسة كـ `Bearer Token`:
* **Accept:** `application/json`
* **Authorization:** `Bearer {your_access_token}`

---

## 1. بيانات الملف الشخصي (Get Profile)
* **الرابط:** `GET https://kiwi.ws/api/profile`
* **المحمية؟:** نعم (طلب مصادقة)
* **الوصف:** استرجاع تفاصيل الحساب الحالي للمستخدم بالكامل.

### 📥 الاستجابة الناجحة (HTTP 200)
```json
{
    "success": true,
    "message": "بيانات الملف الشخصي",
    "data": {
        "id": 67,
        "name": "أحمد محمد",
        "email": "ahmed@example.com",
        "phone": "0512345678",
        "address": "الرياض، المملكة العربية السعودية",
        "account_type": "individual",
        "avatar": "/uploads/avatars/1716189922_664ac322a3d02.png",
        "cover": "/uploads/covers/1716189955_cover_664ac34321d.png",
        "bio": "مهتم بمزادات السيارات النادرة والعقارات التجارية.",
        "latitude": 24.7136,
        "longitude": 46.6753,
        "bank_name": "البنك الأهلي السعودي",
        "bank_account_name": "أحمد محمد علي",
        "bank_account_number": "1000000123456789",
        "bank_iban": "SA1234567890123456789012",
        "status": "active",
        "created_at": "2026-05-18T12:00:00.000000Z",
        "updated_at": "2026-05-19T21:40:00.000000Z"
    }
}
```

---

## 2. تحديث البيانات الشخصية (Update Profile Info)
* **الرابط:** `POST https://kiwi.ws/api/profile/update`
* **المحمية؟:** نعم
* **الوصف:** تحديث الاسم، الجوال، البريد الإلكتروني، العنوان، السيرة الذاتية والموقع الجغرافي.

### 📤 معايير المدخلات (Validation Rules):
* `name`: مطلوب، نص، بحد أقصى 255 حرف.
* `phone`: مطلوب، يجب أن يتكون من 9 أرقام ويبدأ بـ 5 أو 05 (مثال: `05XXXXXXXX` أو `5XXXXXXXX`). فريد باستثناء حساب المستخدم الحالي.
* `email`: مطلوب، صيغة بريد صحيحة، فريد باستثناء حساب المستخدم الحالي.
* `address`: اختياري، نص.
* `bio`: اختياري، نص.
* `latitude` / `longitude`: اختياري، قيمة رقمية.

### 📥 طلب تجريبي (Request Body)
```json
{
    "name": "أحمد محمد علي",
    "phone": "0599999999",
    "email": "ahmed.new@example.com",
    "address": "جدة، حي الروضة",
    "bio": "شغوف بالمزايدة والمزادات السريعة",
    "latitude": 21.4858,
    "longitude": 39.1925
}
```

### 📤 الاستجابة الناجحة (HTTP 200)
```json
{
    "success": true,
    "message": "تم تحديث البيانات الشخصية بنجاح",
    "data": {
        "id": 67,
        "name": "أحمد محمد علي",
        "phone": "0599999999",
        "email": "ahmed.new@example.com",
        "address": "جدة، حي الروضة",
        "bio": "شغوف بالمزايدة والمزادات السريعة",
        "latitude": 21.4858,
        "longitude": 39.1925
    }
}
```

---

## 3. تحديث كلمة المرور (Update Password)
* **الرابط:** `POST https://kiwi.ws/api/profile/password`
* **المحمية؟:** نعم
* **الوصف:** تغيير كلمة المرور الحالية بكلمة مرور جديدة بعد التحقق.

### 📤 معايير المدخلات (Validation Rules):
* `current_password`: مطلوب، كلمة المرور الحالية للمستخدم.
* `password`: مطلوب، كلمة المرور الجديدة، لا تقل عن 8 أحرف.
* `password_confirmation`: مطلوب، يجب أن تطابق كلمة المرور الجديدة تماماً.

### 📥 طلب تجريبي (Request Body)
```json
{
    "current_password": "OldPassword123",
    "password": "NewPassword123",
    "password_confirmation": "NewPassword123"
}
```

### 📤 الاستجابة الناجحة (HTTP 200)
```json
{
    "success": true,
    "message": "تم تحديث كلمة المرور بنجاح",
    "data": null
}
```

---

## 4. تحديث البيانات البنكية (Update Bank Details)
* **الرابط:** `POST https://kiwi.ws/api/profile/bank`
* **المحمية؟:** نعم
* **الوصف:** تحديث معلومات البنك والآيبان للمستخدم مع التحقق التلقائي والدقيق للآيبان ورقم الحساب والعنوان.

### 📤 معايير المدخلات والتحقق (Strict Validation Rules):
* `bank_name`: اختياري، نص، بحد أقصى 255 حرف.
* `bank_account_name`: اختياري، نص، بحد أقصى 255 حرف.
* `bank_account_number`: اختياري، أرقام فقط بطول يتراوح بين 10 إلى 20 رقماً (تعبير نمطي: `^[0-9]{10,20}$`).
* `bank_iban`: اختياري، يجب أن يبدأ بـ `SA` متبوعاً بـ 22 رقماً بالضبط (تعبير نمطي: `^SA[0-9]{22}$`). يقوم السيرفر تلقائياً بإزالة الفراغات الفاصلة وتحويل الحروف إلى حروف كبيرة (Capitalized) قبل الحفظ والتحقق.
* `bank_address`: اختياري، نص، بحد أقصى 500 حرف (عنوان البنك / فرع البنك).

### 📥 طلب تجريبي (Request Body)
```json
{
    "bank_name": "مصرف الراجحي",
    "bank_account_name": "هاجر أحمد المحدث",
    "bank_account_number": "123456789012345",
    "bank_iban": "SA 80 1234 5678 9012 3456 7890",
    "bank_address": "الرياض - فرع الياسمين"
}
```

### 📤 الاستجابة الناجحة (HTTP 200)
```json
{
    "success": true,
    "message": "تم تحديث البيانات البنكية بنجاح",
    "data": {
        "bank_name": "مصرف الراجحي",
        "bank_account_name": "هاجر أحمد المحدث",
        "bank_account_number": "123456789012345",
        "bank_iban": "SA8012345678901234567890",
        "bank_address": "الرياض - فرع الياسمين"
    }
}
```

---

## 5. رفع الصورة الشخصية وصورة الغلاف (Avatar & Cover)
* **الصورة الشخصية (Avatar):** `POST https://kiwi.ws/api/profile/avatar`
* **صورة الغلاف (Cover):** `POST https://kiwi.ws/api/profile/cover`
* **المحمية؟:** نعم
* **نوع البيانات المرسلة (Request Type):** `multipart/form-data`

### 📤 معايير المدخلات (Validation Rules):
* لـ `avatar`: مطلوب، ملف صورة مسموح بصيغ (`jpeg`, `png`, `jpg`, `gif`, `svg`, `webp`) وحجم لا يتجاوز 2 ميجابايت (`max:2048`).
* لـ `cover`: مطلوب، ملف صورة مسموح بصيغ (`jpeg`, `png`, `jpg`, `gif`, `svg`, `webp`) وحجم لا يتجاوز 5 ميجابايت (`max:5120`).
* **ملاحظة أمان وهيكلة:** يتم حفظ الصورة القديمة تلقائياً وحذفها من السيرفر لعدم تراكم الملفات المهملة، ويتم الحفظ مباشرة في المسارات العامة:
  * الصور الشخصية: `public/uploads/avatars/`
  * صور الغلاف: `public/uploads/covers/`

### 📥 استجابة رفع الصورة الشخصية الناجحة (HTTP 200)
```json
{
    "success": true,
    "message": "تم تحديث الصورة الشخصية بنجاح",
    "data": {
        "avatar": "https://kiwi.ws/uploads/avatars/1716189922_664ac322a3d02.png"
    }
}
```

### 📥 استجابة رفع صورة الغلاف الناجحة (HTTP 200)
```json
{
    "success": true,
    "message": "تم تحديث صورة الغلاف بنجاح",
    "data": {
        "cover": "https://kiwi.ws/uploads/covers/1716189955_cover_664ac34321d.png"
    }
}
```

---

## 6. تعديل الاهتمامات (Update Interests)
* **الرابط:** `POST https://kiwi.ws/api/update-interests`
* **المحمية؟:** نعم
* **الوصف:** مزامنة وتعديل الاهتمامات التي اختارها المستخدم (التصنيفات الرئيسية التي يفضل متابعتها).

### 📤 معايير المدخلات (Validation Rules):
* `category_ids`: مطلوب، مصفوفة أرقام (Array of integers).
* `category_ids.*`: يجب أن تكون معرفات تصنيفات موجودة مسبقاً في جدول `categories` (`exists:categories,id`).

### 📥 طلب تجريبي (Request Body)
```json
{
    "category_ids": [1, 3, 5]
}
```

### 📤 الاستجابة الناجحة (HTTP 200)
```json
{
    "success": true,
    "message": "تم تحديث الاهتمامات بنجاح",
    "data": null
}
```

---

## 7. نظام التنبيهات الذكي (Notifications System)

تسهيلاً لمطور التطبيق لتجاوز أي مشاكل حظر لطلب الـ `DELETE` على بعض السيرفرات، تم توفير خيارين لتنفيذ الحذف المباشر بنجاح تام!

### 7.1. جلب قائمة التنبيهات (Get Notifications)
* **الرابط:** `GET https://kiwi.ws/api/notifications`
* **الوصف:** استرجاع جميع التنبيهات الخاصة بالمستخدم مرتبة من الأحدث للأقدم مع ترقيم الصفحات (Pagination).

### 7.2. تحديد التنبيه كمقروء (Mark as Read)
* **الرابط:** `POST https://kiwi.ws/api/notifications/{id}/read`
* **الوصف:** تحويل حالة التنبيه المحدد إلى مقروء وحفظ توقيت القراءة.

### 7.3. تحديد كل التنبيهات كمقروءة (Mark All as Read)
* **الرابط:** `POST https://kiwi.ws/api/notifications/read-all`
* **الوصف:** وضع علامة "مقروء" على كافة التنبيهات غير المقروءة دفعة واحدة.

### 7.4. حذف تنبيه معين (Delete Notification)
لتفادي أي حظر لبروتوكول الـ `DELETE` من قبل خوادم الاستضافة، يقبل السيرفر الآن كلا الطريقتين:

1. **الطريقة الأولى (القياسية):**
   * **الرابط:** `DELETE https://kiwi.ws/api/notifications/{id}`
2. **الطريقة الثانية (الآمنة والبديلة):**
   * **الرابط:** `POST https://kiwi.ws/api/notifications/{id}`
3. **الطريقة الثالثة (المسار المباشر للحذف):**
   * **الرابط:** `POST https://kiwi.ws/api/notifications/{id}/delete`

### 📤 استجابة الحذف الناجحة (HTTP 200)
```json
{
    "success": true,
    "message": "تم حذف التنبيه بنجاح",
    "data": null
}
```

---
> 💡 **نصيحة لمطور التطبيق:** إذا واجهت حالة `500 Server Error` أو حظر أثناء إرسال طلب `DELETE` لحذف إشعار أو عنصر من السلة، يرجى التبديل ببساطة لاستخدام طلب `POST` على نفس الرابط أو إلحاق `/delete` بالرابط كما هو موضح بالخيارين البديلين أعلاه وسيعمل فوراً وبشكل مثالي.
