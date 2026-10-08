# Product Management & Authentication REST API

RESTful API untuk Manajemen Produk dan Autentikasi yang dibangun dengan **Laravel 13**, **Laravel Sanctum**, **Rate Limiting**, **Caching Layer**, **CORS**, dan **Docker Support**.

---

## 🚀 Fitur Utama

1. **Authentication API (`/api/auth`)**:
   - `POST /api/auth/register`: Registrasi user baru (validasi `username`, `password`, `password_confirmation`).
   - `POST /api/auth/login`: Login user menghasilkan `authentication_token` & `refresh_token`.
   - `POST /api/auth/refresh`: Memperbarui token autentikasi.
   - `POST /api/auth/logout`: Menghapus session/token saat ini.
   - `GET /api/auth/me`: Informasi profil user yang sedang login.
   - **Rate Limiter**: Dibatasi maksimal **3 request per 60 detik** (`429 Too Many Requests`).

2. **Product Management API (`/api/products`)**:
   - `GET /api/products`: Mendapatkan semua produk dengan filter:
     - `?search=keyword` (mencari berdasarkan nama/deskripsi)
     - `?category=Clothes` (filter kategori)
     - `?limit=10&page=1` (pagination)
     - **Basic Cache**: Hasil pencarian dan listing produk di-cache otomatis.
   - `GET /api/products/:id`: Mengambil detail satu produk (dengan Cache & 404 JSON error handling).
   - `POST /api/products`: Menambahkan produk baru (Auth required, validasi `title`, `price`, `category`, `images` min 1 item, auto audit trail `created_by` & `created_by_id`, invalidasi cache).
   - `PUT /api/products/:id`: Memperbarui produk (Auth required, 404 jika tidak ditemukan, auto audit trail `updated_by` & `updated_by_id`, invalidasi cache).
   - `DELETE /api/products/:id`: Menghapus produk (Auth required, 404 jika tidak ditemukan, invalidasi cache).
   - **Rate Limiter Mutating**: Endpoint `POST`, `PUT`, `DELETE` dibatasi **1 request per 5 detik**.

3. **Standar Respon & Error Handling**:
   - Respon validasi gagal mengembalikan status **`400 Bad Request`** dengan detail error JSON.
   - Data tidak ditemukan mengembalikan status **`404 Not Found`** dengan format JSON seragam.
   - Multi-Domain **CORS** diaktifkan untuk semua origin (`*`).

4. **Dokumentasi & Delivery**:
   - **Swagger / OpenAPI UI**: Dapat diakses langsung melalui browser di `/docs` atau `/api/documentation`.
   - **Postman Collection**: Tersedia di file `docs/postman_collection.json`.
   - **Docker Support**: Containerized menggunakan `Dockerfile` & `docker-compose.yml`.

---

## 🛠️ Instalasi & Menjalankan Aplikasi

### Opsi 1: Menjalankan Secara Lokal (Native)

1. **Clone repository**:
   ```bash
   git clone <repository-url>
   cd bts-id
   ```

2. **Install dependensi Composer**:
   ```bash
   composer install
   ```

3. **Setup environment & generate key**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Jalankan migrasi database**:
   ```bash
   php artisan migrate
   ```

5. **Jalankan server pengembangan**:
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

2. **Jalankan migrasi di dalam container**:
   ```bash
   docker compose exec app php artisan migrate
   ```

3. Akses API & Dokumentasi di:
   - **Swagger UI**: `http://localhost:8000/docs`
   - **API Base URL**: `http://localhost:8000/api`

---

## 🧪 Menjalankan Automated Test

Aplikasi dilengkapi dengan test suite lengkap (Authentication, Validation, CRUD, Caching, Audit Trail, & Rate Limiting):

```bash
php artisan test
```

---

## 📖 Dokumentasi API

### 1. Swagger UI
Buka browser dan navigasikan ke `http://localhost:8000/docs` atau `http://localhost:8000/api/documentation` untuk melihat dokumentasi interaktif OpenAPI 3.0.

### 2. Postman Collection
Import file [docs/postman_collection.json](file:///docs/postman_collection.json) ke aplikasi Postman Anda. Collection sudah dilengkapi dengan:
- Environment variables (`base_url`, `token`, `refresh_token`).
- Auto-token assignment script pada request Login.
- Contoh body request dan parameter query lengkap.

---

## 📂 Struktur Data Produk

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

---

## 📄 Lisensi
Proyek ini dibuat untuk pemenuhan teknis RESTful API dan dilisensikan di bawah [MIT License](LICENSE).
