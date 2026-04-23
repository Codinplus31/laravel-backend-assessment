#  Vendor Product & Inventory Management System API

> A production-ready RESTful API built with **Laravel 12** for a multi-vendor product system with inventory management and atomic order simulation.

Built as part of the **Losode Backend Developer Assessment**.

---

##  Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Architecture](#-architecture)
- [Setup Instructions](#-setup-instructions)
- [API Documentation](#-api-documentation)
- [Postman Collection](#-postman-collection)
- [Database Design](#-database-design)
- [Design Decisions](#-design-decisions)
- [Screening Questions](#-screening-questions)
- [Seeded Test Data](#-seeded-test-data)

---

##  Features

| Feature | Description |
|---------|-------------|
|  Vendor Authentication | Register, login, logout with Laravel Sanctum token-based auth |
|  Product Management | Full CRUD operations with ownership authorization |
|  Public Product Access | View active products, search by name, pagination |
|  Inventory Management | Concurrent-safe stock updates with pessimistic locking |
|  Order Simulation | Atomic order placement with transaction-based stock management |
|  Caching | Product listing cache with automatic invalidation |
|  Docker Support | Full Docker setup with PHP-FPM, Nginx, and MySQL |
|  Feature Tests | Comprehensive test coverage for all endpoints (28 tests) |

---

##  Tech Stack

| Technology | Purpose |
|------------|---------|
| PHP 8.2 | Runtime |
| Laravel 12 | Framework |
| Laravel Sanctum | API Token Authentication |
| MySQL 8.0 | Database |
| Docker & Docker Compose | Containerization |
| PHPUnit | Testing |

---

##  Architecture

This project follows the **Service/Repository** pattern to keep controllers thin and business logic testable:

```
HTTP Request → Controller → Service → Repository → Model → Database
```

| Layer | Responsibility |
|-------|---------------|
| **Controllers** | Handle HTTP requests/responses, delegate to services |
| **Form Requests** | Input validation and authorization |
| **Services** | Business logic, caching, transactions |
| **Repositories** | Data access layer, query building, pessimistic locking |
| **Models** | Eloquent relationships and query scopes |
| **Traits** | Reusable API response formatting |

<details>
<summary> Click to expand directory structure</summary>

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php            # Register, Login, Logout
│   │   ├── ProductController.php         # Vendor CRUD
│   │   ├── PublicProductController.php   # Public product access
│   │   └── OrderController.php           # Order placement
│   └── Requests/
│       ├── RegisterRequest.php
│       ├── LoginRequest.php
│       ├── StoreProductRequest.php
│       ├── UpdateProductRequest.php
│       └── PlaceOrderRequest.php
├── Models/
│   ├── Vendor.php
│   ├── Product.php
│   └── Order.php
├── Providers/
│   ├── AppServiceProvider.php
│   └── RepositoryServiceProvider.php     # Interface → Implementation bindings
├── Repositories/
│   ├── Interfaces/
│   │   ├── ProductRepositoryInterface.php
│   │   └── OrderRepositoryInterface.php
│   ├── ProductRepository.php
│   └── OrderRepository.php
├── Services/
│   ├── AuthService.php
│   ├── ProductService.php
│   └── OrderService.php
└── Traits/
    └── ApiResponseTrait.php
```

</details>

---

##  Setup Instructions

### Option 1: Local Setup

**Prerequisites:** PHP 8.2+, Composer, MySQL 8.0+

```bash
# 1. Clone the repository
git clone https://github.com/Codinplus31/laravel-backend-assessment.git
cd vendor-product-api

# 2. Install dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env
#    DB_CONNECTION=mysql
#    DB_HOST=127.0.0.1
#    DB_PORT=3306
#    DB_DATABASE=vendor_product_api
#    DB_USERNAME=root
#    DB_PASSWORD=

# 5. Run migrations and seed the database
php artisan migrate --seed

# 6. Start the development server
php artisan serve
```

> The API will be available at **http://localhost:8000/api**

### Option 2: Docker Setup

**Prerequisites:** Docker and Docker Compose

```bash
# 1. Clone the repository
git clone https://github.com/Codinplus31/laravel-backend-assessment.git
cd vendor-product-api

# 2. Configure environment
cp .env.example .env
# Update .env: DB_HOST=db, DB_USERNAME=laravel, DB_PASSWORD=secret

# 3. Build and start containers
docker-compose up -d --build

# 4. Generate app key
docker-compose exec app php artisan key:generate

# 5. Run migrations and seed
docker-compose exec app php artisan migrate --seed
```

> The API will be available at **http://localhost:8000/api**

### Running Tests

```bash
# Local
php artisan test

# Docker
docker-compose exec app php artisan test
```

---

## 📖 API Documentation

### Base URL

```
http://localhost:8000/api
```

### Response Format

All endpoints return a consistent JSON structure:

**Success Response:**
```json
{
    "success": true,
    "message": "Description of result",
    "data": {}
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error description",
    "errors": {}
}
```

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| `200` | Success |
| `201` | Created |
| `401` | Unauthenticated |
| `403` | Forbidden (not your resource) |
| `404` | Not Found |
| `422` | Validation Error / Business Logic Error |
| `500` | Server Error |

---

###  Authentication

#### Register a Vendor

```http
POST /api/register
Content-Type: application/json
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | max:255 |
| `email` | string | Yes | valid email, unique |
| `password` | string | Yes | min:8 |
| `password_confirmation` | string | Yes | must match password |

<details>
<summary> Example Request & Response</summary>

**Request:**
```json
{
    "name": "Fashion Hub Nigeria",
    "email": "fashionhub@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Vendor registered successfully.",
    "data": {
        "vendor": {
            "id": 1,
            "name": "Fashion Hub Nigeria",
            "email": "fashionhub@example.com"
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

</details>

#### Login

```http
POST /api/login
Content-Type: application/json
```

| Field | Type | Required |
|-------|------|----------|
| `email` | string | Yes |
| `password` | string | Yes |

<details>
<summary> Example Response</summary>

**Response (200):**
```json
{
    "success": true,
    "message": "Login successful.",
    "data": {
        "vendor": {
            "id": 1,
            "name": "Fashion Hub Nigeria",
            "email": "fashionhub@example.com"
        },
        "token": "2|xyz789...",
        "token_type": "Bearer"
    }
}
```

</details>

#### Logout

```http
POST /api/logout
Authorization: Bearer {token}
```

---

###  Vendor Product Management (Authenticated)

>  All vendor routes require the header: `Authorization: Bearer {token}`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/vendor/products` | List all your products (paginated) |
| `POST` | `/api/vendor/products` | Create a new product |
| `GET` | `/api/vendor/products/{id}` | View a single product |
| `PUT` | `/api/vendor/products/{id}` | Update a product (partial updates supported) |
| `DELETE` | `/api/vendor/products/{id}` | Delete a product |

#### Create Product Fields

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `name` | string | Yes | max:255 |
| `description` | string | No | max:5000 |
| `price` | numeric | Yes | min:0.01 |
| `stock_quantity` | integer | Yes | min:0 |
| `status` | string | No | `active` or `inactive` (default: `active`) |

> **Note:** Vendors can only manage their own products. Attempting to access another vendor's product returns `403 Forbidden`.

---

###  Public Product Access (No Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/products` | List all active products (paginated, cached) |
| `GET` | `/api/products?search=ankara` | Search products by name |
| `GET` | `/api/products?per_page=5&page=2` | Custom pagination |
| `GET` | `/api/products/{id}` | View a single active product |

> **Note:** Only products with `status: active` are visible. Inactive products return `404`.

---

###  Order Simulation (No Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/orders` | Place an order |
| `GET` | `/api/orders/{id}` | View an order |

#### Place Order Fields

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `product_id` | integer | Yes | must exist in products table |
| `customer_name` | string | Yes | max:255 |
| `customer_email` | string | Yes | valid email |
| `quantity` | integer | Yes | min:1 |

<details>
<summary> Example Request & Response</summary>

**Request:**
```json
{
    "product_id": 1,
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "quantity": 2
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "Order placed successfully.",
    "data": {
        "id": 1,
        "product_id": 1,
        "customer_name": "John Doe",
        "customer_email": "john@example.com",
        "quantity": 2,
        "total_price": "30000.00",
        "status": "completed",
        "product": {
            "id": 1,
            "name": "Ankara Print Maxi Dress",
            "price": "15000.00"
        }
    }
}
```

</details>

---

##  Postman Collection

A ready-to-use Postman collection is included in the project root for quick API testing.

###  File

```
 project-root/
└── Vendor_Product_API.postman_collection.json
```

### How to Import

1. Open **Postman**
2. Click **Import** (top-left corner)
3. Drag and drop `Vendor_Product_API.postman_collection.json` or click **Upload Files**
4. The collection **"Vendor Product & Inventory API"** will appear in your sidebar

### What's Included — 28 Requests

| Folder | # | Description |
|--------|---|-------------|
|  Authentication | 5 | Register, Login (both seeded vendors), Invalid login, Logout |
|  Vendor Products | 8 | Full CRUD, ownership tests, negative stock test |
|  Public Products | 7 | List all, paginated, search by name, view active/inactive/missing |
|  Orders | 7 | Success, insufficient stock, inactive product, validation errors, view |
|  Edge Cases | 4 | No token, invalid token, duplicate email, invalid price |

###  Auto Token Management

The collection includes **automatic token saving**:

- When you run **Register** or **Login**, the API token is automatically saved to the `{{token}}` collection variable
- All authenticated requests use `{{token}}` in their `Authorization` header
- **No manual copy-pasting needed** — just login and start testing!

###  Recommended Testing Flow

```
1. Login          → Run "Login Vendor (Fashion Hub)" to get a token
2. List Products  → Run "List My Products" to see the vendor's products
3. Create Product → Run "Create Product" to add a new one
4. Public Access  → Run "List All Active Products" (no auth needed)
5. Place Order    → Run "Place Order - Success" to test the order flow
6. Edge Cases     → Run the error-handling requests to verify validation
```

### Collection Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `base_url` | `http://localhost:8000/api` | Change if your server runs on a different port |
| `token` | *(empty)* | Auto-populated on login/register |

---

##  Database Design

### Entity Relationship

```
vendors              products              orders
┌──────────────┐     ┌──────────────────┐   ┌─────────────────┐
│ id (PK)      │     │ id (PK)          │   │ id (PK)         │
│ name         │────▶│ vendor_id (FK)   │──▶│ product_id (FK) │
│ email (UQ)   │     │ name             │   │ customer_name   │
│ password     │     │ description      │   │ customer_email  │
│ created_at   │     │ price            │   │ quantity        │
│ updated_at   │     │ stock_quantity   │   │ total_price     │
└──────────────┘     │ status           │   │ status          │
                     │ created_at       │   │ created_at      │
                     │ updated_at       │   │ updated_at      │
                     └──────────────────┘   └─────────────────┘
```

**Relationships:**
- A **Vendor** has many **Products** (one-to-many)
- A **Product** has many **Orders** (one-to-many)

**Key Design Points:**
- `stock_quantity` uses `UNSIGNED INT` — prevents negative values at the database level
- Indexes on `products.name`, `products.status`, and `products(vendor_id, status)` for fast queries
- Foreign key constraints with `CASCADE` delete for referential integrity

---

##  Design Decisions

### 1. Service/Repository Pattern
Logic is separated from controllers into dedicated **Service** and **Repository** classes. This makes the codebase testable, maintainable, and follows the **Single Responsibility Principle**. Controllers stay thin — they only handle HTTP concerns.

### 2. Pessimistic Locking for Inventory
Stock updates use `SELECT ... FOR UPDATE` (pessimistic locking) inside database transactions. This prevents **race conditions** where two concurrent requests could read the same stock value and both succeed, leading to overselling.

### 3. Atomic Order Processing
Orders are placed within a `DB::transaction()` block that:
1. **Locks** the product row (`FOR UPDATE`)
2. **Validates** stock availability
3. **Decrements** stock with a `WHERE stock >= quantity` guard
4. **Creates** the order record

If any step fails, the entire transaction rolls back — no partial state.

### 4. Separate Vendor Model
Instead of using Laravel's default `User` model, a dedicated **Vendor** model is used. This provides clarity and allows the system to evolve with different user types (customers, admins) without conflating roles.

### 5. Caching Strategy
Public product listings are cached for **60 seconds**. Cache is invalidated whenever a product is created, updated, deleted, or when an order modifies stock. This balances **performance** with **data freshness**.

### 6. Consistent API Responses
A shared `ApiResponseTrait` ensures every endpoint returns the same JSON structure (`success`, `message`, `data`), making frontend integration predictable and reliable.

---

##  Screening Questions

### 1. How would you handle two users ordering the last item at the same time?

The system uses **pessimistic locking** (`SELECT ... FOR UPDATE`) combined with **database transactions**:

1. The **first request** acquires a row-level lock on the product
2. The **second request** is blocked (waits) until the first completes
3. The first request decrements stock from `1 → 0` and commits
4. The second request now reads the updated stock (`0`), finds it insufficient, and returns a `422` error

Additionally, the stock decrement uses an atomic `UPDATE ... WHERE stock_quantity >= quantity` clause as a **secondary safety net**, ensuring stock can never go below zero.

### 2. What is a database transaction and why is it important?

A database transaction is a sequence of operations executed as a **single atomic unit** — either all succeed and are committed, or if any fails, all changes are rolled back.

In this system, transactions are critical for order placement:
1. Check stock availability
2. Decrement stock
3. Create the order record

Without a transaction, a failure between step 2 and 3 would leave stock decremented without a corresponding order (**lost inventory**). Transactions guarantee these operations succeed or fail together, preserving **data integrity**.

### 3. How would you scale this system if traffic increases significantly?

| Strategy | Description |
|----------|-------------|
| **Read Replicas** | Route read queries to MySQL replicas, writes to primary |
| **Redis Caching** | Replace file cache with Redis for faster reads and cache tags |
| **Queue Processing** | Move emails and non-critical ops to background queues (Laravel Horizon) |
| **Database Indexing** | Already implemented on frequently queried columns |
| **Rate Limiting** | Apply per-endpoint rate limiting to prevent abuse |
| **Horizontal Scaling** | Run multiple app containers behind a load balancer (K8s/Docker Swarm) |
| **Optimistic Locking** | Use version counters for high-contention products to reduce lock waits |
| **API Gateway** | Use Kong or AWS API Gateway for auth caching and request throttling |

---

##  Seeded Test Data

The database seeder creates sample data for immediate testing:

| Vendor | Email | Password | Products |
|--------|-------|----------|----------|
| Fashion Hub Nigeria | `fashionhub@example.com` | `password123` | 7 products |
| Luxe Accessories | `luxeaccessories@example.com` | `password123` | 6 products |

> Use these credentials to test the authenticated endpoints after running `php artisan migrate --seed`.

---

##  License

This project is open-sourced for assessment purposes.
