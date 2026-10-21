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

<h1 align="center">Mazad — Real-time Auction & Multi-Vendor Marketplace Platform</h1>

<p align="center">
  A comprehensive, enterprise-ready digital auction and marketplace platform built with <strong>Laravel 13</strong>, <strong>MySQL</strong>, <strong>Pusher WebSockets</strong>, and <strong>Firebase Cloud Messaging</strong>.
</p>

---

## 📌 Table of Contents

- [Overview](#-overview)
- [Key Features](#-key-features)
- [Architecture & Tech Stack](#-architecture--tech-stack)
- [Project Directory Structure](#-project-directory-structure)
- [Database & Eloquent Models](#-database--eloquent-models)
- [API Architecture & Endpoints](#-api-architecture--endpoints)
- [Real-time Events & WebSockets](#-real-time-events--websockets)
- [Installation & Setup](#-installation--setup)
- [Configuration & Environment Variables](#-configuration--environment-variables)
- [Testing](#-testing)
- [Security & Best Practices](#-security--best-practices)
- [License](#-license)

---

## 📖 Overview

**Mazad** is an end-to-end auction engine and classifieds e-commerce platform. It provides dynamic bidding mechanisms, multi-vendor listing workflows, direct checkout, real-time negotiation, escrow-backed partner service contracts, and automated notification pipelines.

Engineered with performance and scalability in mind, it separates public mobile/frontend API interfaces (`/api`) from an administrative command center (`/admin`), backed by token-based authentication (Sanctum) and event broadcasting (Pusher).

---

## ✨ Key Features

### 🔨 1. Advanced Auction & Bidding Engine
- **Multiple Auction Modes**:
  - **Online Auctions**: Timed digital bidding with configurable minimum bid increments and duration rules.
  - **Hybrid Auctions**: Combines on-site and remote live bidding.
  - **Direct Purchase (Buy It Now)**: Instant checkout option bypassing the auction process.
- **Automated Bidding Logic**:
  - Min/Max bid limits with automatic outbid validation.
  - Real-time limit threshold alert triggers.
  - Seller counter-offer negotiations (`Accept`, `Reject`, `Counter`).

### 📦 2. Listings & Dynamic Attributes
- Multi-category support (Real Estate, Vehicles, Electronics, Heavy Equipment, etc.).
- Dynamic per-category specification properties (custom metadata attributes per category).
- Multi-image media uploads and legal/technical attachment handling.
- Geo-coordinates, city/district geolocation indexing, and proximity search.
- Sponsored and promoted ads processing.

### 💬 3. Real-time Chat & In-App Messaging
- Direct peer-to-peer conversations between buyers and sellers.
- Instant delivery powered by **Pusher WebSockets** (`conversation.{id}` private channels).
- Attachment and image sharing in chat threads.
- Chat management: pinning, muting, reporting, and user blocking.

### 🔐 4. Secure Orders, Payments & Escrow
- Complete order lifecycle: `Pending Payment` ➔ `Paid` ➔ `Delivered` ➔ `Received`.
- **OTP Delivery Verification**: Unique delivery authentication codes required from the buyer to release settlements.
- **Partner Services & Escrow Checkout**: Secure partner contract handling with escrow settlement hold and release upon verified service completion.

### 🔔 5. Push Notification Pipeline
- Integrated with **Firebase Cloud Messaging (FCM)** via Google Service Account credentials.
- Multi-device token management per user.
- Asynchronous dispatching via queued background jobs (`SendBulkNotificationsJob`).
- Granular per-user notification preferences (SMS, push, auction updates).

### 👥 6. Multi-Tier Users & Team Collaboration
- Accounts tailored for: **Individuals**, **Commercial Companies**, and **Authorized Partners / Representatives**.
- **Team Management**: Business accounts can invite team members with granular permission scopes.

### 📊 7. Administrative Command Center
- Full-featured web admin dashboard for business operations.
- Moderation tools: ad approvals, reports inspection, banned user filters.
- Financial settlement tracking, fee commissions, and transaction logs.
- CMS features: dynamic banners, FAQs, policy guides, and support ticketing.

---

## 🛠️ Architecture & Tech Stack

| Layer | Technology | Details |
|---|---|---|
| **Backend Framework** | **Laravel 13.x** | Modern PHP MVC architecture, Queues, Events |
| **Language Runtime** | **PHP 8.3+** | Strict types, match expressions, JIT performance |
| **Database** | **MySQL 8.x** | Relational schema, spatial geometry indices |
| **API Authentication** | **Laravel Sanctum 4.x** | Secure bearer token issuance and expiration |
| **Real-time Engine** | **Pusher PHP Server 7.x** | WebSocket event broadcasting |
| **Push Notifications** | **Firebase FCM v1 API** | Multi-platform mobile notification delivery |
| **Frontend & Assets** | **Vite 8.x + Tailwind CSS 4.x** | Fast build tooling and modern UI system |
| **Queue & Worker** | **Laravel Database Queue** | Asynchronous email, bulk notifications & tasks |

---

## 📂 Project Directory Structure

```plaintext
Mazad/
├── app/
│   ├── Events/                     # Broadcast events (e.g., MessageSent)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/              # 20 Admin management controllers
│   │   │   └── Api/                # 19 REST API controllers for mobile/SPA
│   │   └── Middleware/             # Token auth, banned user verification
│   ├── Jobs/                       # Background jobs (SendBulkNotificationsJob)
│   ├── Models/                     # 34 Eloquent ORM entity definitions
│   └── Services/                   # External integrations (FcmService, etc.)
├── bootstrap/                      # Application bootstrapping & providers
├── config/                         # Environment & runtime configurations
├── database/
│   ├── factories/                  # Model factory definitions
│   ├── migrations/                 # 81 Database schema migrations
│   └── seeders/                    # Realistic test data seeders
├── public/                         # Public entry point (index.php) and assets
├── resources/                      # Blade admin views, localization, CSS & JS
├── routes/
│   ├── api.php                     # RESTful API routing endpoints
│   ├── web.php                     # Administration panel web routes
│   └── channels.php                # Private Pusher broadcast authorization
└── tests/                          # Automated Unit and Feature test suites
```

---

## 🗃️ Database & Eloquent Models

The platform is powered by **34 Eloquent Models** orchestrating relational business domains:

| Domain | Key Models | Responsibility |
|---|---|---|
| **Users & Security** | `User`, `BlockedUser` | User profiles, account tiers, verification states |
| **Ads & Catalog** | `Ad`, `AdImage`, `AdAttachment`, `Category`, `CategoryPage`, `Property`, `AdProperty` | Listing items, media galleries, dynamic specs |
| **Auctions & Bidding** | `Bid`, `Question`, `AdQuestion` | Live bid records, limits, Q&A on lots |
| **Orders & Commerce** | `Order`, `Cart`, `ServiceOrder` | Direct sales, cart checkout, partner services |
| **Communication** | `Conversation`, `Message`, `Notification` | WebSocket chat channels, push messages |
| **Financials** | `Transaction`, `Commission`, `FinancialSettlement` | Commission fees, settlement payouts |
| **Helpdesk & Support** | `Ticket`, `TicketLog`, `Faq`, `Policy`, `TermCondition`, `Review` | Support tickets, customer ratings, CMS |
| **Geography** | `Region`, `City` | Regional clustering and location-based discovery |

---

## 🔌 API Architecture & Endpoints

All client interactions utilize JSON payload exchanges with standard HTTP verbs and status codes:

### Base Request Headers
```http
Authorization: Bearer <your_sanctum_token>
Accept: application/json
Content-Type: application/json
```

### Key API Endpoint Groups

#### 1. Authentication & Onboarding
- `POST /api/register` — New account registration with OTP trigger
- `POST /api/login` — Phone/Email credential authentication
- `POST /api/verify-otp` — Verify SMS activation code
- `POST /api/send-otp` — Resend verification token

#### 2. Ads & Catalog Search
- `GET /api/home` — Aggregated homepage feed (featured, urgent, recent auctions)
- `GET /api/categories` — Nested categories tree with metadata
- `GET /api/search` — Filtered search engine (price range, city, auction type, dynamic props)
- `POST /api/ads` — Create a new listing lot with images & attachments

#### 3. Bidding & Auction Interactions
- `POST /api/ads/{id}/bids` — Place a bid on a live auction lot
- `GET /api/bids-offers` — Retrieve incoming bids & offers received by seller
- `POST /api/bids-offers/{id}/accept` — Accept winning or negotiated bid
- `POST /api/bids-offers/{id}/counter` — Send seller counter-proposal

#### 4. Cart & Purchase Management
- `POST /api/ads/{id}/orders` — Instant direct purchase order
- `GET /api/my-purchases` — User order history with status tracking
- `GET /api/my-sales` — Seller sales dashboard
- `POST /api/orders/{id}/confirm-delivery` — Complete handover using security delivery code

#### 5. Real-time Messaging
- `GET /api/chats` — Inbox conversations list with unread counter
- `POST /api/chats/start` — Initiate or resume chat with lot owner
- `POST /api/chats/{id}/send` — Send instant message with file attachment

> 💡 *For exhaustive API specifications, request schemas, and sample JSON responses, refer to the project documentation file [`API_Documentation.md`](./API_Documentation.md).*

---

## 📡 Real-time Events & WebSockets

Broadcast authorization is defined in `routes/channels.php`:

```php
// Private individual notification channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private conversation channel between participants
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = \App\Models\Conversation::find($conversationId);
    return $conversation && (
        (int)$conversation->user_one_id === (int)$user->id || 
        (int)$conversation->user_two_id === (int)$user->id
    );
});
```

---

## 🚀 Installation & Setup

### Prerequisites
- **PHP** >= 8.3 with extensions (`pdo_mysql`, `mbstring`, `openssl`, `curl`, `bcmath`)
- **Composer** 2.x
- **Node.js** >= 18.x & **npm**
- **MySQL Server** 8.x

### Quickstart Guide

```bash
# 1. Clone the repository
git clone https://github.com/hagarahmed68896/Mazad.git
cd Mazad

# 2. Install PHP and Node dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Configure database credentials in .env, then run migrations
php artisan migrate --seed

# 5. Create storage symlink
php artisan storage:link

# 6. Build frontend assets
npm run build
```

### Running Locally

You can launch the complete ecosystem using Laravel's parallel orchestration script:

```bash
composer dev
```

> **Note:** `composer dev` starts the HTTP server, the asynchronous background queue worker, the Pail log monitor, and the Vite dev server concurrently.

Alternatively, run each process in dedicated terminals:
```bash
php artisan serve          # Laravel HTTP Server (http://localhost:8000)
php artisan queue:listen   # Queue Worker for notifications and orders
npm run dev                # Vite Dev Server
```

---

## ⚙️ Configuration & Environment Variables

Key parameters required in `.env`:

```ini
APP_NAME=Mazad
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mazad
DB_USERNAME=root
DB_PASSWORD=your_secure_password

# Broadcasting (Pusher WebSockets)
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID="your_pusher_app_id"
PUSHER_APP_KEY="your_pusher_key"
PUSHER_APP_SECRET="your_pusher_secret"
PUSHER_APP_CLUSTER="mt1"

# Queue Driver
QUEUE_CONNECTION=database

# Firebase Cloud Messaging
FIREBASE_PROJECT_ID="your-firebase-project-id"
FIREBASE_CREDENTIALS_PATH="storage/credentials/firebase.json"
```

---

## 🧪 Testing

Execute test suites using PHPUnit / Pest:

```bash
# Run all automated tests
php artisan test

# Run specific feature tests
php artisan test --filter=OrderTest
```

---

## 🔒 Security & Best Practices

- **Token Safety**: API routes enforce `auth:sanctum` and a custom `check.banned` middleware pipeline.
- **Credential Hygiene**: Sensitive keys (Firebase Service Account files, Pusher secrets, and database credentials) must strictly stay within `.env` and `.gitignore`.
- **Escrow Safeguards**: Seller payout transactions are guarded by delivery confirmation tokens to eliminate fraud.

---

## 📄 License

This project is licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

<p align="center">
  <strong>Mazad Auction Platform</strong> • Built with ❤️ using Laravel 13
</p>
