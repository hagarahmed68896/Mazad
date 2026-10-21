<p align="center">
  <img src="https://img.shields.io/badge/Mazad-Auction%20Platform-E63946?style=for-the-badge&logo=laravel&logoColor=white" alt="Mazad Logo">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Sanctum-Auth-38B2AC?style=flat-square&logo=laravel&logoColor=white" alt="Sanctum">
  <img src="https://img.shields.io/badge/Pusher-WebSockets-300D4F?style=flat-square&logo=pusher&logoColor=white" alt="Pusher">
  <img src="https://img.shields.io/badge/Firebase-FCM-FFCA28?style=flat-square&logo=firebase&logoColor=black" alt="Firebase FCM">
</p>

<h1 align="center">🔨 مزاد — Mazad</h1>

<p align="center">
  <strong>منصة مزادات إلكترونية متكاملة — Full-Stack Auction & Marketplace Platform</strong>
</p>

---

## 📋 نظرة عامة — Overview

**مزاد (Mazad)** هي منصة مزادات وتجارة إلكترونية متكاملة مبنية على **Laravel 13**، تتيح للمستخدمين إنشاء إعلانات، المشاركة في مزادات إلكترونية وهجينة، الشراء المباشر، إدارة المبيعات والمشتريات، والتواصل عبر نظام محادثات فوري.

**Mazad** is a comprehensive auction and e-commerce platform built on **Laravel 13**. It enables users to create ads, participate in electronic and hybrid auctions, buy directly, manage sales and purchases, and communicate through a real-time chat system.

---

## ✨ المميزات الرئيسية — Key Features

### 🏷️ إدارة الإعلانات — Ads Management
- إنشاء، تعديل، حذف، وإعادة نشر الإعلانات
- دعم أنواع متعددة: **شراء مباشر** | **مزاد إلكتروني** | **مزاد هجين**
- رفع صور ومرفقات متعددة لكل إعلان
- خصائص ديناميكية حسب الفئة (Dynamic Properties)
- نظام ترويج ودفع للإعلانات (Promotion & Payment)

### 🔨 نظام المزادات — Auction System
- مزادات إلكترونية مع تحديث فوري عبر **Pusher WebSockets**
- نظام المزايدة مع حد أدنى وأقصى وزيادات محددة
- عروض مضادة من البائع (Counter Offers)
- قبول ورفض العروض والسومات
- إشعارات عند الاقتراب من الحد الأقصى

### 🛒 الشراء والطلبات — Orders & Purchases
- نظام شراء مباشر (Buy Now)
- سلة مشتريات (Shopping Cart)
- إدارة المشتريات والمبيعات مع فلاتر متقدمة
- كود تسليم (Delivery Code) لتوثيق الاستلام
- نظام دفع عبر التحويل البنكي

### 💬 المحادثات الفورية — Real-time Chat
- محادثات مباشرة بين البائع والمشتري
- دعم الرسائل النصية، الصور، والمرفقات
- تثبيت وكتم المحادثات
- حظر المستخدمين والإبلاغ عنهم
- بث فوري عبر **Pusher** (Broadcasting)

### 🔔 الإشعارات — Notifications
- إشعارات فورية عبر **Firebase Cloud Messaging (FCM)**
- إرسال إشعارات جماعية عبر Background Jobs
- إعدادات إشعارات مخصصة لكل مستخدم
- دعم أنواع متعددة من الإشعارات

### ⭐ المفضلة والاطلاع لاحقاً — Wishlist & Look Later
- إضافة/إزالة الإعلانات من المفضلة
- قائمة "اطلاع لاحقاً" منفصلة
- بحث وفلترة داخل القوائم المحفوظة

### 🏢 إدارة الفريق — Team Management
- إضافة أعضاء فريق لإدارة الحساب
- صلاحيات وأدوار متعددة

### 🤝 خدمات الشركاء — Partner Services
- إنشاء وإدارة خدمات المندوبين/الشركاء
- نظام Checkout مع ضمان (Escrow System)
- إدارة طلبات الخدمة وإتمامها

### 📊 التقارير والتحليلات — Reports & Analytics
- لوحة تحكم شاملة للأدمن (Admin Dashboard)
- تقارير الإعلانات، المبيعات، والمستخدمين
- إدارة العمولات والتسويات المالية

### 🛡️ لوحة تحكم المشرف — Admin Panel
- إدارة المستخدمين والأقسام والمدن
- إدارة البانرات الإعلانية
- إدارة الأسئلة الشائعة والسياسات
- نظام تذاكر الدعم الفني
- إعدادات عامة للمنصة

---

## 🏗️ البنية التقنية — Tech Stack

| التقنية | الإصدار | الاستخدام |
|---------|---------|-----------|
| **PHP** | 8.3+ | Backend Runtime |
| **Laravel** | 13.x | Backend Framework |
| **MySQL** | - | Database |
| **Laravel Sanctum** | 4.x | API Authentication (Token-based) |
| **Pusher** | 7.x | Real-time WebSockets |
| **Firebase FCM** | - | Push Notifications |
| **Vite** | 8.x | Frontend Build Tool |
| **Tailwind CSS** | 4.x | Frontend Styling |

---

## 📂 هيكل المشروع — Project Structure

```
Mazad/
├── app/
│   ├── Console/             # Artisan Commands
│   ├── Events/              # Broadcasting Events (MessageSent)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/         # 19 API Controllers (Mobile/Frontend)
│   │   │   └── Admin/       # 20 Admin Panel Controllers
│   │   └── ...
│   ├── Jobs/                # Queue Jobs (Bulk Notifications)
│   ├── Models/              # 34 Eloquent Models
│   ├── Providers/           # Service Providers
│   └── Services/            # Business Logic (FCM Service)
├── config/                  # Configuration Files
├── database/
│   ├── factories/           # Model Factories
│   ├── migrations/          # 81 Database Migrations
│   └── seeders/             # Database Seeders
├── lang/                    # Localization (Arabic/English)
├── public/                  # Public Assets
├── resources/               # Views, CSS, JS
├── routes/
│   ├── api.php              # RESTful API Routes
│   ├── web.php              # Web Routes (Admin Panel)
│   ├── channels.php         # Broadcasting Channels
│   └── console.php          # Scheduled Tasks
├── storage/                 # File Storage & Logs
└── tests/                   # Unit & Feature Tests
```

---

## 🗃️ نماذج البيانات — Data Models (34 Model)

| النموذج | الوصف |
|---------|-------|
| `User` | المستخدمين (فرد، شركة، مندوب) |
| `Ad` | الإعلانات (مزاد، شراء مباشر، هجين) |
| `AdImage` / `AdAttachment` | صور ومرفقات الإعلانات |
| `AdProperty` / `Property` | خصائص ديناميكية للإعلانات |
| `AdQuestion` / `Question` | أسئلة وأجوبة الإعلانات |
| `Category` / `CategoryPage` | الأقسام والفئات |
| `Region` / `City` | المناطق والمدن |
| `Bid` | المزايدات والعروض |
| `Order` | طلبات الشراء |
| `Cart` | سلة المشتريات |
| `Conversation` / `Message` | المحادثات والرسائل |
| `Notification` | الإشعارات |
| `Favorite` / `LookLater` | المفضلة / اطلاع لاحقاً |
| `Review` | التقييمات والمراجعات |
| `Service` / `ServiceOrder` | خدمات الشركاء |
| `Banner` | البانرات الإعلانية |
| `Ticket` / `TicketLog` | تذاكر الدعم الفني |
| `Transaction` / `Commission` | المعاملات والعمولات |
| `FinancialSettlement` | التسويات المالية |
| `BlockedUser` | قائمة الحظر |
| `Faq` / `Policy` / `TermCondition` | الأسئلة الشائعة، السياسات، والشروط |
| `Setting` | إعدادات المنصة |

---

## 🔌 API Endpoints

### 🔓 Public Endpoints
| Method | Endpoint | الوصف |
|--------|----------|-------|
| `POST` | `/api/login` | تسجيل الدخول |
| `POST` | `/api/register` | إنشاء حساب جديد |
| `POST` | `/api/send-otp` | إرسال رمز التحقق |
| `POST` | `/api/verify-otp` | التحقق من الرمز |
| `POST` | `/api/forgot-password` | نسيت كلمة المرور |
| `POST` | `/api/reset-password` | إعادة تعيين كلمة المرور |
| `GET` | `/api/home` | الصفحة الرئيسية |
| `GET` | `/api/categories` | قائمة الأقسام |
| `GET` | `/api/search` | البحث المتقدم |
| `GET` | `/api/ads/{id}` | تفاصيل الإعلان |
| `GET` | `/api/regions` | المناطق |
| `GET` | `/api/cities` | المدن |
| `GET` | `/api/faqs` | الأسئلة الشائعة |
| `GET` | `/api/privacy-policy` | سياسة الخصوصية |
| `GET` | `/api/terms-conditions` | الشروط والأحكام |

### 🔐 Protected Endpoints (Require Auth)
| Module | Endpoints | الوصف |
|--------|-----------|-------|
| **Profile** | 10 endpoints | إدارة الملف الشخصي، الصورة، البنك، الإشعارات |
| **Ads** | 7 endpoints | إنشاء، تعديل، حذف، إعادة نشر الإعلانات |
| **Bids & Offers** | 5 endpoints | المزايدة، قبول، رفض، عروض مضادة |
| **Orders** | 6 endpoints | الشراء، المبيعات، المشتريات، توثيق الاستلام |
| **Cart** | 5 endpoints | إضافة، تحديث، حذف، إفراغ، الدفع |
| **Chat** | 5 endpoints | المحادثات، إرسال رسائل، حظر |
| **Wishlist** | 2 endpoints | عرض المفضلة، إضافة/إزالة |
| **Look Later** | 2 endpoints | عرض القائمة، إضافة/إزالة |
| **Reviews** | 6 endpoints | إضافة، رد، تعديل، حذف التقييمات |
| **Q&A** | 3 endpoints | أسئلة وأجوبة الإعلانات |
| **Team** | 5 endpoints | إدارة أعضاء الفريق |
| **Services** | 4 endpoints | إدارة خدمات الشركاء |
| **Service Orders** | 4 endpoints | طلبات الخدمات (Escrow) |
| **Notifications** | 4 endpoints | عرض، قراءة، حذف الإشعارات |
| **Reports** | 1 endpoint | التقارير والتحليلات |

> 📄 للتوثيق التفصيلي لكل endpoint، راجع ملف [`API_Documentation.md`](./API_Documentation.md)

---

## ⚙️ التثبيت والإعداد — Installation & Setup

### المتطلبات — Prerequisites

- PHP >= 8.3
- Composer
- MySQL
- Node.js & npm
- حساب Pusher (للمحادثات الفورية)
- مشروع Firebase (للإشعارات)

### خطوات التثبيت — Installation Steps

**1. استنساخ المشروع:**
```bash
git clone <repository-url> mazad
cd mazad
```

**2. تثبيت التبعيات:**
```bash
composer install
npm install
```

**3. إعداد ملف البيئة:**
```bash
cp .env.example .env
php artisan key:generate
```

**4. تعديل ملف `.env`:**
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mazad
DB_USERNAME=root
DB_PASSWORD=your_password

# Pusher (Real-time)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1

# Firebase FCM
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_CREDENTIALS_PATH=/path/to/firebase-credentials.json
```

**5. تهيئة قاعدة البيانات:**
```bash
php artisan migrate
php artisan db:seed   # (اختياري)
```

**6. تشغيل المشروع:**
```bash
# تشغيل الكل معاً (Server + Queue + Vite)
composer dev

# أو تشغيل كل خدمة على حدة:
php artisan serve          # Laravel Server
php artisan queue:listen   # Queue Worker
npm run dev                # Vite Dev Server
```

> 💡 **ملاحظة:** الأمر `composer dev` يشغل 4 عمليات متوازية:
> - Laravel Server (المنفذ 8000)
> - Queue Worker (للوظائف المجدولة)
> - Pail Logger (سجلات مباشرة)
> - Vite Dev Server (للأصول الأمامية)

---

## 🔐 المصادقة — Authentication

يستخدم المشروع **Laravel Sanctum** للمصادقة عبر التوكن:

```
Authorization: Bearer <your_sanctum_token>
Accept: application/json
Content-Type: application/json
```

### نظام OTP
- إرسال رمز تحقق عبر SMS
- التحقق من الرمز لتفعيل الحساب
- نظام الأجهزة المعروفة (Known Devices)

---

## 📡 البث الفوري — Real-time Broadcasting

### القنوات المتاحة — Available Channels

| القناة | الوصف |
|--------|-------|
| `App.Models.User.{id}` | إشعارات خاصة بالمستخدم |
| `conversation.{id}` | رسائل المحادثة الفورية |

### الأحداث — Events

| الحدث | الوصف |
|-------|-------|
| `MessageSent` | إرسال رسالة جديدة في المحادثة |

---

## 🧪 الاختبارات — Testing

```bash
# تشغيل جميع الاختبارات
composer test

# أو مباشرة
php artisan test
```

---

## 📜 الرخصة — License

هذا المشروع مرخص تحت رخصة [MIT](https://opensource.org/licenses/MIT).

---

<p align="center">
  <strong>🔨 مزاد — Mazad Platform</strong><br>
  <em>Built with ❤️ using Laravel 13</em>
</p>
