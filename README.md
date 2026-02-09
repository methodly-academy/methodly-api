# Methodly API

Sistem Backend untuk aplikasi Methodly, dibangun dengan Laravel 12.

## Persiapan Proyek

1. **Clone repositori**
2. **Instal dependensi**
   ```bash
   composer install
   npm install
   ```
3. **Konfigurasi Environment**
   Salin `.env.example` menjadi `.env` dan sesuaikan pengaturan database Anda.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. **Migrasi Database**
   ```bash
   php artisan migrate --seed
   ```
5. **Jalankan Server**
   ```bash
   php artisan serve
   ```

## Dokumentasi API (Swagger)

Proyek ini menggunakan Swagger untuk dokumentasi endpoint. Anda dapat mengaksesnya melalui:

**`http://localhost:8000/api/documentation`**

### Fitur Dokumentasi:
- Definisi semua endpoint (v1)
- Uji coba langsung (Try it out)
- Autentikasi Bearer Token (Sanctum)
- Skema request dan response dalam Bahasa Indonesia

## Lisensi

[MIT license](https://opensource.org/licenses/MIT).
