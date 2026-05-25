# 📘 دليل توثيق الـ API لصفحات مشترياتي ومنتجات للاطلاع لاحقاً (API Documentation)

يحتوي هذا الملف على توثيق شامل ومنظم لجميع الـ API Endpoints الجديدة الخاصة بإدارة المشتريات وقائمة المنتجات المحفوظة للاطلاع لاحقاً، واللازمة للاستخدام والربط مع كافة المعاملات والتحليلات البرمجية.

---

## 🔑 معلومات عامة ولوازم الاتصال (Authentication & Headers)

يجب إرفاق الـ Headers التالية في كل الطلبات المرسلة إلى الـ API:

| اسم الـ Header | القيمة | الوصف |
| :--- | :--- | :--- |
| `Authorization` | `Bearer <your_sanctum_token>` | توكن تسجيل الدخول الخاص بالمستخدم (Sanctum Token) |
| `Accept` | `application/json` | لتحديد نوع الاستجابة كـ JSON وتفادي التوجيه للـ Web |
| `Content-Type` | `application/json` | عند إرسال بيانات في جسم الطلب (Request Body) |

---

## 1️⃣ إدارة المشتريات والطلبات (Purchases & Orders Management)

### 1️⃣ جلب قائمة المشتريات (Get My Purchases)

* **الرابط:** `/api/my-purchases`
* **نوع الطلب:** `GET`
* **المعاملات الاختيارية (Query Parameters):**
  * `status`: تصفية حسب تبويب المشتريات:
    * `all`: يعرض جميع مشتريات وطلبات المستخدم (الافتراضي).
    * `auctions`: يعرض الطلبات من نوع مزاد إلكتروني أو هجين الذي فاز بها المستخدم.
    * `direct_purchase`: يعرض الطلبات من نوع الشراء المباشر الفوري.
  * `q`: البحث بالكلمة المفتاحية في عناوين الإعلانات.
  * `city`: تصفية حسب معرف المدينة.
  * `category`: تصفية حسب معرف الفئة الرئيسية.
  * `sort`: ترتيب النتائج:
    * `newest`: من الأحدث للأقدم (الافتراضي).
    * `price_high`: من الأعلى سعراً للأقل سعراً.
    * `price_low`: من الأقل سعراً للأعلى سعراً.

#### 📝 أمثلة تفصيلية لحالات الاستخدام (Use Case Examples):

### 📌 1. الاستجابة الافتراضية (Default Response - بدون معلمات)
* **الرابط:** `/api/my-purchases`
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة مشترياتي",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 12,
                "order_number": "ORD-PAID-1",
                "ad_id": 45,
                "buyer_id": 3,
                "seller_id": 2,
                "final_price": 33519,
                "status": "paid", 
                "delivery_code": "839201",
                "created_at": "2026-05-24T14:30:00.000000Z",
                "ad": {
                    "id": 45,
                    "title": "لابتوب ديل XPS 15 شاشة تعمل باللمس",
                    "price": 33000,
                    "type": "direct_purchase",
                    "image": "/uploads/ads/dell_xps.png",
                    "category": { "id": 5, "name": "إلكترونيات" },
                    "city": { "id": 1, "name": "الرياض" }
                },
                "seller": { "id": 2, "name": "البائع المميز" }
            }
        ],
        "total": 1
    }
}
```

---

### 📌 2. مثال التصفية بالفلاتر (Filter Example - حسب المدينة والقسم)
* **الطلب:** `GET /api/my-purchases?city=1&category=5` (المدينة: الرياض [1]، القسم: إلكترونيات [5])
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة مشترياتي",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 12,
                "order_number": "ORD-PAID-1",
                "ad_id": 45,
                "buyer_id": 3,
                "seller_id": 2,
                "final_price": 33519,
                "status": "paid", 
                "delivery_code": "839201",
                "created_at": "2026-05-24T14:30:00.000000Z",
                "ad": {
                    "id": 45,
                    "title": "لابتوب ديل XPS 15 شاشة تعمل باللمس",
                    "price": 33000,
                    "type": "direct_purchase",
                    "image": "/uploads/ads/dell_xps.png",
                    "category": { "id": 5, "name": "إلكترونيات" },
                    "city": { "id": 1, "name": "الرياض" }
                },
                "seller": { "id": 2, "name": "البائع المميز" }
            }
        ],
        "total": 1
    }
}
```

---

### 📌 3. مثال البحث بالكلمة المفتاحية (Search Example - بكلمة سيارة)
* **الطلب:** `GET /api/my-purchases?q=سيارة` (البحث عن كلمة "سيارة")
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة مشترياتي",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 20,
                "order_number": "ORD-RCVD-1",
                "ad_id": 88,
                "buyer_id": 3,
                "seller_id": 5,
                "final_price": 2800519,
                "status": "received", 
                "delivery_code": "154263",
                "created_at": "2026-05-24T11:00:00.000000Z",
                "ad": {
                    "id": 88,
                    "title": "سيارة بي إم دبليو M4 كوبيه رياضية",
                    "price": 2800000,
                    "type": "hybrid_auction",
                    "image": "/uploads/ads/bmw_m4.png",
                    "category": { "id": 2, "name": "سيارات" },
                    "city": { "id": 1, "name": "الرياض" }
                },
                "seller": { "id": 5, "name": "معرض النخبة للسيارات" }
            }
        ],
        "total": 1
    }
}
```

---

### 📌 4. مثال الفرز حسب التبويبات (Tabs Example - تبويب المزادات فقط)
* **الطلب:** `GET /api/my-purchases?status=auctions` (عرض المزادات التي فاز بها المشتري)
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة مشترياتي",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 20,
                "order_number": "ORD-RCVD-1",
                "ad_id": 88,
                "buyer_id": 3,
                "seller_id": 5,
                "final_price": 2800519,
                "status": "received", 
                "delivery_code": "154263",
                "created_at": "2026-05-24T11:00:00.000000Z",
                "ad": {
                    "id": 88,
                    "title": "سيارة بي إم دبليو M4 كوبيه رياضية",
                    "price": 2800000,
                    "type": "hybrid_auction",
                    "image": "/uploads/ads/bmw_m4.png",
                    "category": { "id": 2, "name": "سيارات" },
                    "city": { "id": 1, "name": "الرياض" }
                },
                "seller": { "id": 5, "name": "معرض النخبة للسيارات" }
            }
        ],
        "total": 1
    }
}
```

---

### 📌 5. مثال الترتيب والفرز (Sorting Example - الأعلى سعراً أولاً)
* **الطلب:** `GET /api/my-purchases?sort=price_high` (عرض المشتريات مرتبة من الأعلى سعراً للأقل)
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة مشترياتي",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 20,
                "order_number": "ORD-RCVD-1",
                "ad_id": 88,
                "buyer_id": 3,
                "seller_id": 5,
                "final_price": 2800519,
                "status": "received", 
                "delivery_code": "154263",
                "created_at": "2026-05-24T11:00:00.000000Z",
                "ad": {
                    "id": 88,
                    "title": "سيارة بي إم دبليو M4 كوبيه رياضية",
                    "price": 2800000,
                    "type": "hybrid_auction",
                    "image": "/uploads/ads/bmw_m4.png",
                    "category": { "id": 2, "name": "سيارات" },
                    "city": { "id": 1, "name": "الرياض" }
                },
                "seller": { "id": 5, "name": "معرض النخبة للسيارات" }
            },
            {
                "id": 12,
                "order_number": "ORD-PAID-1",
                "ad_id": 45,
                "buyer_id": 3,
                "seller_id": 2,
                "final_price": 33519,
                "status": "paid", 
                "delivery_code": "839201",
                "created_at": "2026-05-24T14:30:00.000000Z",
                "ad": {
                    "id": 45,
                    "title": "لابتوب ديل XPS 15 شاشة تعمل باللمس",
                    "price": 33000,
                    "type": "direct_purchase",
                    "image": "/uploads/ads/dell_xps.png",
                    "category": { "id": 5, "name": "إلكترونيات" },
                    "city": { "id": 1, "name": "الرياض" }
                },
                "seller": { "id": 2, "name": "البائع المميز" }
            }
        ],
        "total": 2
    }
}
```

---

### 2️⃣ تقديم طلب شراء إعلان جديد (Create Buy Now Order)

* **الرابط:** `/api/ads/{ad_id}/orders`
* **نوع الطلب:** `POST`
* **جسم الطلب (Request Body):**
```json
{
    "payment_method": "bank_transfer"
}
```

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم تقديم طلب الشراء بنجاح! رقم الطلب: #ORD-A8FB",
    "data": {
        "order_number": "ORD-A8FB",
        "final_price": 33519,
        "status": "pending_payment"
    }
}
```

---

## 2️⃣ قائمة المفضلة (Wishlist / Favorites API)

### 1️⃣ جلب قائمة "المفضلة" (Get Wishlist)

* **الرابط:** `/api/wishlist`
* **نوع الطلب:** `GET`
* **المعاملات الاختيارية (Query Parameters):**
  * `q`: البحث بالكلمة المفتاحية.
  * `city_id`: تصفية النتائج حسب معرف المدينة.
  * `category_id`: تصفية النتائج حسب معرف الفئة.
  * `type`: نوع الإعلان (`auction` للمزادات أو `buy_now` للشراء الفوري).
  * `sort`: الترتيب (`newest` الأحدث، `price_high` الأعلى، `price_low` الأقل).

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "قائمة المفضلة",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 88,
                "title": "فيلا سكنية فاخرة في حي حطين شمال الرياض",
                "price": 5000000,
                "type": "electronic_auction",
                "image": "/uploads/ads/villa.png",
                "views_count": 120,
                "bids_count": 14,
                "is_favorited": true,
                "is_look_later": false,
                "category": { "id": 1, "name": "عقارات" },
                "city": { "id": 1, "name": "الرياض" }
            }
        ],
        "total": 1
    }
}
```

---

### 2️⃣ إضافة أو إزالة منتج من المفضلة (Toggle Wishlist Item)

* **الرابط:** `/api/wishlist/toggle/{ad_id}`
* **نوع الطلب:** `POST`

#### 📝 مثال على الاستجابة الناجحة عند الإضافة (Added):
```json
{
    "success": true,
    "status": "success",
    "action": "added",
    "message": "تم إضافة الإعلان إلى المفضلة",
    "data": {
        "ad_id": 88,
        "is_favorite": true
    }
}
```

#### 📝 مثال على الاستجابة الناجحة عند الإزالة (Removed):
```json
{
    "success": true,
    "status": "success",
    "action": "removed",
    "message": "تم إزالة الإعلان من المفضلة",
    "data": {
        "ad_id": 88,
        "is_favorite": false
    }
}
```

---

## 3️⃣ قائمة منتجات للاطلاع لاحقاً (Look Later API)

### 1️⃣ جلب قائمة "منتجات للاطلاع لاحقاً" (Get Look Later List)

* **الرابط:** `/api/look-later`
* **نوع الطلب:** `GET`
* **المعاملات الاختيارية (Query Parameters):**
  * `q`: البحث بالكلمة المفتاحية.
  * `city_id`: تصفية النتائج حسب معرف المدينة.
  * `category_id`: تصفية النتائج حسب معرف الفئة.
  * `type`: نوع الإعلان (`auction` للمزادات أو `buy_now` للشراء الفوري).
  * `sort`: الترتيب (`newest` الأحدث، `price_high` الأعلى، `price_low` الأقل).

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "قائمة المنتجات المحفوظة للاطلاع لاحقاً",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 45,
                "title": "لابتوب ديل XPS 15 شاشة تعمل باللمس",
                "price": 33000,
                "type": "direct_purchase",
                "image": "/uploads/ads/dell_xps.png",
                "views_count": 85,
                "bids_count": 0,
                "is_favorited": false,
                "is_look_later": true,
                "category": { "id": 5, "name": "إلكترونيات" },
                "city": { "id": 1, "name": "الرياض" }
            }
        ],
        "total": 1
    }
}
```

---

### 2️⃣ إضافة أو إزالة منتج من قائمة الاطلاع لاحقاً (Toggle Look Later Item)

* **الرابط:** `/api/look-later/toggle/{ad_id}`
* **نوع الطلب:** `POST`

#### 📝 مثال على الاستجابة الناجحة عند الإضافة (Added):
```json
{
    "success": true,
    "status": "success",
    "action": "added",
    "message": "تم إضافة الإعلان لقائمة الاطلاع لاحقاً",
    "data": {
        "ad_id": 45,
        "is_look_later": true
    }
}
```

#### 📝 مثال على الاستجابة الناجحة عند الإزالة (Removed):
```json
{
    "success": true,
    "status": "success",
    "action": "removed",
    "message": "تم إزالة الإعلان من قائمة الاطلاع لاحقاً",
    "data": {
        "ad_id": 45,
        "is_look_later": false
    }
}
```

---

## 3️⃣ إجراءات أمان الطلبات والتوثيق (Order Security & OTP)

### 1️⃣ توثيق استلام الطلب من قبل البائع (Confirm Order Delivery)

* **الرابط:** `/api/orders/{order_id}/confirm-delivery`
* **نوع الطلب:** `POST`
* **جسم الطلب (Request Body):**
```json
{
    "delivery_code": "839201"
}
```

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم توثيق استلام الطلب بنجاح! 🎉",
    "data": {
        "id": 12,
        "order_number": "ORD-PAID-1",
        "status": "received", 
        "final_price": 33519
    }
}
```

---

## 4️⃣ إدارة المزادات والعروض للبائع (Bids & Offers Seller API)

### 1️⃣ جلب قائمة السومات والعروض المستلمة (Get Received Bids & Offers)

* **الرابط:** `/api/bids-offers`
* **نوع الطلب:** `GET`
* **المعاملات الاختيارية (Query Parameters):**
  * `tab`: التبويب المطلوب (`offers` للعروض أو `auctions` للمزادات).
  * `status`: التصفية حسب الحالة (`all` الافتراضي، `accepted` تم الاتفاق، `rejected` لم يتم الاتفاق، `active` جاري التفاوض).
  * `q`: البحث بالاسم أو العنوان.
  * `category_id`: التصفية بالقسم.
  * `advertiser_type`: نوع المزايد (`individual` فرد، `company` شركة، `partner` مندوب).

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "قائمة المزايدات والعروض الواردة",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 25,
                "ad_id": 45,
                "user_id": 3,
                "amount": 35000,
                "status": "active",
                "created_at": "2026-05-24T17:30:00.000000Z",
                "ad": {
                    "id": 45,
                    "title": "لابتوب ديل XPS 15 شاشة تعمل باللمس",
                    "price": 33000,
                    "type": "direct_purchase"
                },
                "user": {
                    "id": 3,
                    "name": "أحمد المشتري",
                    "account_type": "individual"
                }
            }
        ],
        "total": 1
    }
}
```

#### 📝 أمثلة تفصيلية لحالات الاستخدام (Use Case Examples):

### 📌 1. مثال الاستجابة عند التصفية بتبويب المزادات (Auctions Tab Example)
* **الطلب:** `GET /api/bids-offers?tab=auctions`
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة المزايدات والعروض الواردة",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 31,
                "ad_id": 88,
                "user_id": 5,
                "amount": 2800000,
                "status": "active",
                "created_at": "2026-05-24T11:00:00.000000Z",
                "ad": {
                    "id": 88,
                    "title": "سيارة بي إم دبليو M4 كوبيه رياضية",
                    "price": 2800000,
                    "type": "hybrid_auction"
                },
                "user": {
                    "id": 5,
                    "name": "معرض النخبة للسيارات",
                    "account_type": "company"
                }
            }
        ],
        "total": 1
    }
}
```

---

### 📌 2. مثال البحث بالكلمة المفتاحية (Search Example - البحث عن "باسم")
* **الطلب:** `GET /api/bids-offers?q=باسم`
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة المزايدات والعروض الواردة",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 15,
                "ad_id": 88,
                "user_id": 4,
                "amount": 2750000,
                "status": "outbid",
                "created_at": "2026-05-24T10:45:00.000000Z",
                "ad": {
                    "id": 88,
                    "title": "سيارة بي إم دبليو M4 كوبيه رياضية",
                    "price": 2800000,
                    "type": "hybrid_auction"
                },
                "user": {
                    "id": 4,
                    "name": "باسم المشتري",
                    "account_type": "individual"
                }
            }
        ],
        "total": 1
    }
}
```

---

### 📌 3. مثال التصفية بحالة العروض (Filter Example - العروض المقبولة "تم الاتفاق")
* **الطلب:** `GET /api/bids-offers?status=accepted`
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة المزايدات والعروض الواردة",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 12,
                "ad_id": 45,
                "user_id": 3,
                "amount": 33500,
                "status": "accepted",
                "created_at": "2026-05-24T14:30:00.000000Z",
                "ad": {
                    "id": 45,
                    "title": "لابتوب ديل XPS 15 شاشة تعمل باللمس",
                    "price": 33000,
                    "type": "direct_purchase"
                },
                "user": {
                    "id": 3,
                    "name": "أحمد المشتري",
                    "account_type": "individual"
                }
            }
        ],
        "total": 1
    }
}
```

---

### 📌 4. مثال التصفية المركبة بالفلاتر (Filter Example - الشركات لقسم السيارات)
* **الطلب:** `GET /api/bids-offers?category_id=2&advertiser_type=company` (القسم: سيارات [2]، المعلن: شركة)
* **الاستجابة:**
```json
{
    "success": true,
    "message": "قائمة المزايدات والعروض الواردة",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 31,
                "ad_id": 88,
                "user_id": 5,
                "amount": 2800000,
                "status": "active",
                "created_at": "2026-05-24T11:00:00.000000Z",
                "ad": {
                    "id": 88,
                    "title": "سيارة بي إم دبليو M4 كوبيه رياضية",
                    "price": 2800000,
                    "type": "hybrid_auction"
                },
                "user": {
                    "id": 5,
                    "name": "معرض النخبة للسيارات",
                    "account_type": "company"
                }
            }
        ],
        "total": 1
    }
}
```

---

### 2️⃣ قبول سومة أو عرض شراء (Accept Bid / Offer)

* **الرابط:** `/api/bids-offers/{bid_id}/accept`
* **نوع الطلب:** `POST`

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم قبول العرض بنجاح 🎉"
}
```

---

### 3️⃣ رفض سومة أو عرض شراء (Reject Bid / Offer)

* **الرابط:** `/api/bids-offers/{bid_id}/reject`
* **نوع الطلب:** `POST`

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم رفض العرض بنجاح."
}
```

---

### 4️⃣ تقديم عرض مضاد من البائع (Submit Seller Counter Offer)

* **الرابط:** `/api/bids-offers/{bid_id}/counter`
* **نوع الطلب:** `POST`
* **جسم الطلب (Request Body):**
```json
{
    "amount": 34500
}
```

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم إرسال العرض المضاد بنجاح.",
    "data": {
        "new_amount": 34500,
        "remaining": 1
    }
}
```

---

## 5️⃣ إدارة الدعم الفني والسياسات والمساعدة (Help Desk, Policies & Support API)

### 1️⃣ جلب الأسئلة الشائعة (Get FAQs)

* **الرابط:** `/api/faqs`
* **نوع الطلب:** `GET`
* **المحمية؟:** لا (طلب عام)

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "قائمة الأسئلة الشائعة",
    "data": [
        {
            "id": 1,
            "question": "كيف يمكنني الاشتراك والمزايدة في المزاد؟",
            "answer": "للمشاركة في أي مزاد، يجب أولاً إنشاء حساب وتفعيله، ثم الانتقال لصفحة المزاد المطلوبة والضغط على زر \"اشترك في المزاد\" ودفع قيمة التأمين المحددة للبدء بتقديم السومات.",
            "status": 1,
            "target_group": "[\"all\"]",
            "created_at": "2026-05-24T22:00:00.000000Z",
            "updated_at": "2026-05-24T22:00:00.000000Z"
        }
    ]
}
```

---

### 2️⃣ جلب سياسة الخصوصية (Get Privacy Policy)

* **الرابط:** `/api/privacy-policy`
* **نوع الطلب:** `GET`
* **المحمية؟:** لا

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "سياسة الخصوصية",
    "data": [
        {
            "id": 1,
            "title": "1. استخدام المنصة",
            "content": "باستخدامك للمنصة، فإنك توافق على استخدام الخدمات بطريقة قانونية ومسؤولة ووفقاً للسياسات المعلنة."
        }
    ]
}
```

---

### 3️⃣ جلب الشروط والأحكام (Get Terms and Conditions)

* **الرابط:** `/api/terms-conditions`
* **نوع الطلب:** `GET`
* **المحمية؟:** لا

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "الشروط والأحكام",
    "data": [
        {
            "id": 1,
            "title": "1. استخدام المنصة",
            "content": "باستخدامك للمنصة، فإنك توافق على استخدام الخدمات بطريقة قانونية ومسؤولة ووفقاً للسياسات المعلنة."
        }
    ]
}
```

---

### 4️⃣ تقديم طلب دعم فني أو استفسار (Submit Support Ticket)

* **الرابط:** `/api/contact-us`
* **نوع الطلب:** `POST`
* **المحمية؟:** لا (يقبل من زوار أو مستخدمين مسجلين)
* **جسم الطلب (Request Body):**
```json
{
    "phone": "0512345678",
    "description": "أواجه مشكلة في تفعيل الحساب وتأمين الاشتراك في مزاد العقارات."
}
```

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم تقديم طلب الدعم بنجاح",
    "data": {
        "ticket_number": "TCK-B91F8A7D",
        "status": "under_review",
        "created_at": "2026-05-24 22:30"
    }
}
```

---

### 5️⃣ جلب استفساراتي ومتابعة التذاكر (Get My Inquiries)

* **الرابط:** `/api/my-inquiries`
* **نوع الطلب:** `GET`
* **المحمية؟:** نعم (`Sanctum Token` مطلوب)

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "قائمة استفساراتي",
    "data": [
        {
            "ticket_number": "TCK-A938F1",
            "reported_about": "كيف تختار العقار؟",
            "description": "أريد معرفة المعايير الأساسية لاختيار العقار المناسب في المزادات وكيفية التثبت من الصكوك.",
            "status": "resolved",
            "status_lbl": "تم الحل",
            "created_at": "2026-05-24 21:30",
            "reply": "تم الرد: لاختيار العقار المناسب، يرجى مراجعة الصك الإلكتروني المرفق في صفحة المزاد، والتنسيق لمعاينة العقار على الطبيعة من خلال رقم ممثل البيع الموضح.",
            "logs": [
                {
                    "action": "resolved",
                    "note": "تم الرد على استفسارك وتزويدك بكافة النصائح المطلوبة.",
                    "created_at": "2026-05-24 21:45"
                }
            ]
        }
    ]
}
```

---

## 6️⃣ إدارة أعضاء الفريق للشركات (Team Members Management API)

جميع الـ Endpoints التالية **محمية** وتتطلب `Authorization: Bearer <token>` وتعمل فقط لحسابات الشركات (`account_type === 'company'`).

### 1️⃣ جلب قائمة أعضاء الفريق (Get Team Members)

* **الرابط:** `/api/settings/team`
* **نوع الطلب:** `GET`
* **المعاملات الاختيارية (Query Parameters):**
  * `tab`: التصفية حسب النوع:
    * `representative` لبطاقات المندوبين (الافتراضي).
    * `partner` لمدراء الحساب.
  * `q`: البحث بالاسم، الهاتف، أو البريد الإلكتروني.

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "قائمة أعضاء الفريق",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 15,
                "name": "أحمد خالد",
                "email": "ahmed.khalid@example.com",
                "phone": "598765431",
                "account_type": "representative",
                "commission_rate": "10.00",
                "avatar": "/uploads/avatars/1716578291_team_60f3d4.png",
                "status": "active",
                "parent_id": 2,
                "ads_count": 8,
                "created_at": "2026-05-24T18:25:33.000000Z"
            }
        ],
        "total": 1
    }
}
```

---

### 2️⃣ إضافة عضو فريق جديد - مندوب أو مدير (Create Team Member)

* **الرابط:** `/api/settings/team`
* **نوع الطلب:** `POST`
* **جسم الطلب (Request Body - multipart/form-data):**
  * `name` (مطلوب - string): الاسم الكامل للمندوب أو المدير.
  * `email` (مطلوب - email - فريد): البريد الإلكتروني.
  * `phone` (مطلوب - رقم هاتف سعودي يبدأ بـ 5 ويتكون من 9 أرقام - فريد).
  * `account_type` (مطلوب - `representative` للمندوب أو `partner` للمدير).
  * `commission_rate` (اختياري للمندوب - numeric بين 0 و 100).
  * `avatar` (اختياري - ملف صورة).

* **ملاحظة**: يتم توليد كلمة مرور افتراضية تلقائياً للعضو الجديد وهي: `password123`.

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم إضافة عضو الفريق بنجاح. كلمة المرور الافتراضية: password123",
    "data": {
        "id": 16,
        "name": "سعد منصور",
        "email": "saad.mansour@example.com",
        "phone": "598765435",
        "account_type": "representative",
        "commission_rate": "5.00",
        "avatar": "/uploads/avatars/1716579291_team_60f3e8.png",
        "status": "active",
        "parent_id": 2,
        "created_at": "2026-05-24T18:30:00.000000Z"
    }
}
```

---

### 3️⃣ تفاصيل عضو فريق محدد (Get Team Member Details)

* **الرابط:** `/api/settings/team/{id}`
* **نوع الطلب:** `GET`

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تفاصيل عضو الفريق",
    "data": {
        "id": 15,
        "name": "أحمد خالد",
        "email": "ahmed.khalid@example.com",
        "phone": "598765431",
        "account_type": "representative",
        "commission_rate": "10.00",
        "avatar": "/uploads/avatars/1716578291_team_60f3d4.png",
        "status": "active",
        "parent_id": 2,
        "created_at": "2026-05-24T18:25:33.000000Z"
    }
}
```

---

### 4️⃣ تعديل بيانات عضو فريق (Update Team Member)

* **الرابط:** `/api/settings/team/{id}/update`
* **نوع الطلب:** `POST` (للسماح برفع الملفات/الصور في PHP)
* **جسم الطلب (Request Body - multipart/form-data):**
  * `name` (مطلوب - string).
  * `email` (مطلوب - email).
  * `phone` (مطلوب - رقم هاتف سعودي يبدأ بـ 5 ويتكون من 9 أرقام).
  * `commission_rate` (اختياري للمندوب - numeric).
  * `avatar` (اختياري - ملف صورة).

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم تحديث بيانات العضو بنجاح.",
    "data": {
        "id": 15,
        "name": "أحمد خالد المعدل",
        "email": "ahmed.khalid.updated@example.com",
        "phone": "598765431",
        "account_type": "representative",
        "commission_rate": "12.00",
        "avatar": "/uploads/avatars/1716578999_team_60f3d4.png",
        "status": "active",
        "parent_id": 2,
        "updated_at": "2026-05-24T18:35:00.000000Z"
    }
}
```

---

### 5️⃣ حذف عضو فريق بالكامل (Delete Team Member)

* **الرابط:** `/api/settings/team/{id}`
* **نوع الطلب:** `DELETE`

#### 📝 مثال على الاستجابة الناجحة (Success Response):
```json
{
    "success": true,
    "message": "تم حذف عضو الفريق بنجاح."
}
```
