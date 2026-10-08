# Product Management & Authentication REST API

RESTful API untuk Manajemen Produk dan Autentikasi yang dibangun menggunakan **Laravel 12 / 13 (PHP 8.4)**, **Laravel Sanctum**, **Rate Limiting**, **Caching Layer**, **CORS**, dan **Docker Support**.

---

## 📋 Daftar Isi
- [Fitur Utama](#-fitur-utama)
- [Spesifikasi Teknis & Dependensi](#-spesifikasi-teknis--dependensi)
- [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
  - [Opsi 1: Menjalankan Secara Lokal (Native)](#opsi-1-menjalankan-secara-lokal-native)
  - [Opsi 2: Menjalankan Menggunakan Docker](#opsi-2-menjalankan-menggunakan-docker)
- [Daftar Endpoint API](#-daftar-endpoint-api)
- [Contoh Request & Response JSON](#-contoh-request--response-json)
  - [1. Autentikasi](#1-autentikasi)
  - [2. Manajemen Produk](#2-manajemen-produk)
  - [3. Format Respon Error](#3-format-respon-error)
- [Dokumentasi API (Postman Collection)](#-dokumentasi-api-postman-collection)
- [Pengujian Otomatis (Automated Testing)](#-pengujian-otomatis-automated-testing)
- [Fitur Khusus & Arsitektur](#-fitur-khusus--arsitektur)
  - [Audit Trail Otomatis](#audit-trail-otomatis)
  - [Caching Layer](#caching-layer)
  - [Rate Limiting (Throttle)](#rate-limiting-throttle)
  - [CORS Multi-Domain](#cors-multi-domain)

---

## 🚀 Fitur Utama

1. **Authentication API (`/api/auth`)**:
   - `POST /api/auth/register`: Pendaftaran user baru (`username`, `password`, `password_confirmation`).
   - `POST /api/auth/login`: Login user menghasilkan `authentication_token` (Bearer) dan `refresh_token`.
   - `POST /api/auth/refresh`: Memperbarui authentication token menggunakan refresh token.
   - `POST /api/auth/logout`: Menghapus session token aktif.
   - `GET /api/auth/me`: Menampilkan profil user yang sedang login.
   - **Rate Limiting**: Maksimal **3 request per 60 detik** (`429 Too Many Requests`).

2. **Product Management API (`/api/products`)**:
   - `GET /api/products`: Mendapatkan semua produk dengan filter:
     - `?search=keyword` : Mencari berdasarkan nama (`title`) atau deskripsi (`description`).
     - `?category=Clothes` : Filter spesifik berdasarkan kategori produk.
     - `?limit=10&page=1` : Pagination data produk.
     - **Basic Cache**: Hasil pencarian dan listing produk di-cache otomatis untuk efisiensi performa.
   - `GET /api/products/:id`: Mengambil detail 1 produk (dengan cache & 404 JSON error response).
   - `POST /api/products`: Menambahkan produk baru (Auth required, validasi `title`, `price`, `category`, `images` min 1 item, auto audit trail `created_by` & `created_by_id`, auto cache invalidation).
   - `PUT /api/products/:id`: Memperbarui data produk (Auth required, 404 jika tidak ditemukan, auto audit trail `updated_by` & `updated_by_id`, auto cache invalidation).
   - `DELETE /api/products/:id`: Menghapus produk (Auth required, 404 jika tidak ditemukan, auto cache invalidation).
   - **Rate Limiting Mutating**: Endpoint `POST`, `PUT`, `DELETE` dibatasi **1 request per 5 detik**.

3. **Standar Respon & Error Handling**:
   - Validasi gagal: Status **`400 Bad Request`** dengan rincian error JSON.
   - Resource tidak ditemukan: Status **`404 Not Found`** dengan struktur JSON seragam.
   - Unauthorized: Status **`401 Unauthorized`**.
   - Rate limit terlampaui: Status **`429 Too Many Requests`**.

4. **Multi-Domain CORS**:
   - Telah dikonfigurasi untuk mengizinkan integrasi dari aplikasi web di berbagai domain (`Origin: *`).

5. **Dokumentasi & Delivery**:
   - **Postman Collection**: File siap pakai di `docs/postman_collection.json`.
   - **Docker Support**: Containerized menggunakan `Dockerfile` & `docker-compose.yml`.

---

## 🛠 Spesifikasi Teknis & Dependensi

- **Bahasa Pemrograman**: PHP 8.4
- **Framework**: Laravel 12+
- **Autentikasi**: Laravel Sanctum (Token-based)
- **Database**: SQLite (default, zero-configuration) atau MySQL / PostgreSQL
- **Containerization**: Docker & Docker Compose

---

## 💻 Panduan Instalasi & Menjalankan

### Opsi 1: Menjalankan Secara Lokal (Native)

1. **Clone repository**:
   ```bash
   git clone <url-repository>
   cd bts-id
   ```

2. **Install dependensi Composer**:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup Database & Jalankan Migrasi**:
   ```bash
   # Buat database sqlite jika belum ada
   touch database/database.sqlite
   
   # Jalankan migrasi dan seeder data awal produk
   php artisan migrate --seed
   ```

5. **Jalankan Server Lokal**:
   ```bash
   php artisan serve
   ```
   Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

### Opsi 2: Menjalankan Menggunakan Docker

1. **Build dan jalankan container**:
   ```bash
   docker compose up -d --build
   ```

2. **Jalankan migrasi & seeder di dalam container**:
   ```bash
   docker compose exec app php artisan migrate --seed
   ```

3. **Akses Layanan**:
   - **API Base URL**: `http://localhost:8000/api`

---

## 📌 Daftar Endpoint API

| Method | Endpoint | Auth | Rate Limit | Deskripsi |
| :--- | :--- | :---: | :---: | :--- |
| `POST` | `/api/auth/register` | ❌ | 3 hit / 60 detik | Pendaftaran user baru |
| `POST` | `/api/auth/login` | ❌ | 3 hit / 60 detik | Login & peroleh `authentication_token` & `refresh_token` |
| `POST` | `/api/auth/refresh` | 🔒 | - | Refresh token autentikasi |
| `POST` | `/api/auth/logout` | 🔒 | - | Logout dan revoke token |
| `GET` | `/api/auth/me` | 🔒 | - | Ambil informasi user yang sedang login |
| `GET` | `/api/products` | ❌ | - | List produk (filter `search`, `category`, `limit`, `page`) |
| `GET` | `/api/products/{id}` | ❌ | - | Detail satu produk berdasarkan ID |
| `POST` | `/api/products` | 🔒 | 1 hit / 5 detik | Tambah produk baru (wajib `title`, `price`, `category`, `images`) |
| `PUT` | `/api/products/{id}` | 🔒 | 1 hit / 5 detik | Update data produk berdasarkan ID |
| `DELETE` | `/api/products/{id}` | 🔒 | 1 hit / 5 detik | Hapus produk berdasarkan ID |

> 🔒 *Memerlukan Header `Authorization: Bearer <authentication_token>`*

---

## 📝 Contoh Request & Response JSON

### 1. Autentikasi

#### A. Register User (`POST /api/auth/register`)
**Request Body:**
```json
{
  "username": "jhon_doe",
  "password": "supersecret",
  "password_confirmation": "supersecret"
}
```

**Response Success (`201 Created`):**
```json
{
  "message": "User registered successfully",
  "user": {
    "id": 1,
    "username": "jhon_doe",
    "created_at": "2026-10-08T04:30:00.000000Z"
  }
}
```

#### B. Login (`POST /api/auth/login`)
**Request Body:**
```json
{
  "username": "jhon_doe",
  "password": "supersecret"
}
```

**Response Success (`200 OK`):**
```json
{
  "message": "Login successful",
  "authentication_token": "1|abcdef123456...",
  "refresh_token": "2|fedcba654321...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "username": "jhon_doe"
  }
}
```

---

### 2. Manajemen Produk

#### A. Get All Products (`GET /api/products?search=shirt&category=Clothes&limit=10&page=1`)

**Response Success (`200 OK`):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Awesome T-Shirt",
      "price": 99.99,
      "description": "High-quality cotton t-shirt",
      "category": "Clothes",
      "images": [
        "https://placeimg.com/640/480/any"
      ],
      "created_at": "2025-01-01 15:01:04",
      "created_by": "Jhon Doe",
      "created_by_id": "1",
      "updated_at": "2025-01-01 15:01:04",
      "updated_by": "Jhon Doe",
      "updated_by_id": "1"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1,
    "last_page": 1
  }
}
```

#### B. Get Detail Product (`GET /api/products/1`)

**Response Success (`200 OK`):**
```json
{
  "id": 1,
  "title": "Awesome T-Shirt",
  "price": 99.99,
  "description": "High-quality cotton t-shirt",
  "category": "Clothes",
  "images": [
    "https://placeimg.com/640/480/any"
  ],
  "created_at": "2025-01-01 15:01:04",
  "created_by": "Jhon Doe",
  "created_by_id": "1",
  "updated_at": "2025-01-01 15:01:04",
  "updated_by": "Jhon Doe",
  "updated_by_id": "1"
}
```

#### C. Create Product (`POST /api/products`)
*Headers: `Authorization: Bearer <authentication_token>`*

**Request Body:**
```json
{
  "title": "Awesome T-Shirt",
  "price": 99.99,
  "description": "High-quality cotton t-shirt",
  "category": "Clothes",
  "images": [
    "https://placeimg.com/640/480/any"
  ]
}
```

**Response Success (`201 Created`):**
```json
{
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "title": "Awesome T-Shirt",
    "price": 99.99,
    "description": "High-quality cotton t-shirt",
    "category": "Clothes",
    "images": [
      "https://placeimg.com/640/480/any"
    ],
    "created_at": "2025-01-01 15:01:04",
    "created_by": "Jhon Doe",
    "created_by_id": "1",
    "updated_at": "2025-01-01 15:01:04",
    "updated_by": "Jhon Doe",
    "updated_by_id": "1"
  }
}
```

#### D. Update Product (`PUT /api/products/1`)
*Headers: `Authorization: Bearer <authentication_token>`*

**Request Body:**
```json
{
  "title": "Awesome T-Shirt Updated",
  "price": 89.99,
  "description": "Updated high-quality cotton t-shirt",
  "category": "Clothes",
  "images": [
    "https://placeimg.com/640/480/any",
    "https://placeimg.com/640/480/tech"
  ]
}
```

**Response Success (`200 OK`):**
```json
{
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "title": "Awesome T-Shirt Updated",
    "price": 89.99,
    "description": "Updated high-quality cotton t-shirt",
    "category": "Clothes",
    "images": [
      "https://placeimg.com/640/480/any",
      "https://placeimg.com/640/480/tech"
    ],
    "created_at": "2025-01-01 15:01:04",
    "created_by": "Jhon Doe",
    "created_by_id": "1",
    "updated_at": "2025-01-01 15:10:00",
    "updated_by": "Jhon Doe",
    "updated_by_id": "1"
  }
}
```

#### E. Delete Product (`DELETE /api/products/1`)
*Headers: `Authorization: Bearer <authentication_token>`*

**Response Success (`200 OK`):**
```json
{
  "message": "Product deleted successfully"
}
```

---

### 3. Format Respon Error

#### Validasi Gagal (`400 Bad Request`)
```json
{
  "message": "Validation error",
  "errors": {
    "title": [
      "The title field is required."
    ],
    "images": [
      "The images field must contain at least 1 items."
    ]
  }
}
```

#### Resource Tidak Ditemukan (`404 Not Found`)
```json
{
  "message": "Product not found"
}
```

#### Rate Limit Terlampaui (`429 Too Many Requests`)
```json
{
  "message": "Too Many Attempts."
}
```

---

## 📖 Dokumentasi API (Postman Collection)

File Postman Collection lengkap telah disediakan pada:
👉 [docs/postman_collection.json](docs/postman_collection.json)

### Cara Menggunakan di Postman:
1. Buka aplikasi **Postman**.
2. Klik tombol **Import** di kiri atas.
3. Drag & drop atau pilih file `docs/postman_collection.json`.
4. Variabel `{{base_url}}` sudah terset secara default ke `http://localhost:8000`.
5. Jalankan request **Login** (`POST /api/auth/login`), script collection akan **secara otomatis menyimpan token** ke variabel `{{token}}`.
6. Semua request lain yang memerlukan authorization siap langsung di-hit tanpa perlu copy-paste token manual!

---

## 🧪 Pengujian Otomatis (Automated Testing)

Project telah dilengkapi dengan Feature Test komprehensif yang mencakup seluruh skenario (Auth, CRUD, Caching, Audit Trail, Validation Status Code, dan Rate Limiting).

Jalankan test suite dengan perintah:
```bash
php artisan test
```
atau
```bash
vendor/bin/phpunit
```

**Hasil Pengujian:**
```text
   PASS  Tests\Feature\AuthTest
  ✓ it can register a new user
  ✓ it validates register input
  ✓ it can login user and return tokens
  ✓ it fails login with wrong credentials
  ✓ it can refresh token
  ✓ it can logout user
  ✓ it can get current user profile
  ✓ it throttles auth requests

   PASS  Tests\Feature\ProductTest
  ✓ it returns paginated products with filters
  ✓ it caches product list queries
  ✓ it returns single product detail
  ✓ it returns 404 json when product not found
  ✓ it creates a product with audit trail
  ✓ it creates product with uploaded file
  ✓ it uploads image via upload endpoint
  ✓ it returns 400 on product validation failure
  ✓ it requires authentication to create product
  ✓ it updates product with audit trail
  ✓ it returns 404 when updating non-existent product
  ✓ it deletes product
  ✓ it cleans up local storage images when product is deleted
  ✓ it returns 404 when deleting non-existent product
  ✓ it clears cache when product is mutated
  ✓ it throttles product mutation requests

Tests:    22 passed (97 assertions)
Duration: 0.98s
```

---

## ⚙️ Fitur Khusus & Arsitektur

### Audit Trail Otomatis
Pada saat membuat (`POST`) atau memperbarui (`PUT`) produk:
- `created_by` dan `created_by_id` otomatis diisi berdasarkan user yang sedang terautentikasi saat dibuat.
- `updated_by` dan `updated_by_id` otomatis diperbarui sesuai user yang melakukan aksi modifikasi.

### Caching Layer
- Response list produk (`GET /api/products`) dan detail produk (`GET /api/products/:id`) otomatis disimpan ke dalam cache.
- Ketika terjadi mutasi data (`POST`, `PUT`, `DELETE`), sistem secara otomatis menginvaliasi / menghapus cache agar data selalu konsisten.

### Rate Limiting (Throttle)
- **Auth Endpoint**: Dibatasi maksimal 3 request per 60 detik per IP.
- **Product Mutation**: Dibatasi maksimal 1 request per 5 detik per user/IP untuk mencegah spam dan duplicate submission.

### CORS Multi-Domain
File konfigurasi `config/cors.php` telah diaktifkan dengan `'allowed_origins' => ['*']` serta mendukung preflight request `OPTIONS` sehingga siap dikonsumsi oleh aplikasi frontend (React, Vue, Angular, Mobile App, dll) dari domain manapun.

---

## 📄 Lisensi
Proyek ini dibuat untuk pemenuhan teknis RESTful API dan dilisensikan di bawah [MIT License](LICENSE).
