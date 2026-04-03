# Presensi Sekolah (Web Admin + API Mobile)

Aplikasi presensi sekolah berbasis Laravel 12 untuk kebutuhan:

- Web admin dan guru (Livewire) untuk pengelolaan data dan monitoring presensi.
- API mobile (Sanctum) untuk siswa melakukan presensi lewat scan QR.

Proyek ini mendukung dua tipe sesi presensi:

- Presensi harian (wali kelas).
- Presensi mata pelajaran (guru pengampu mapel).

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Peran Pengguna](#peran-pengguna)
- [Alur Singkat Sistem](#alur-singkat-sistem)
- [Struktur Modul](#struktur-modul)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi Lokal](#instalasi-lokal)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Migrasi dan Seeder](#migrasi-dan-seeder)
- [Akun Default Seeder](#akun-default-seeder)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Rute Web](#rute-web)
- [Dokumentasi API](#dokumentasi-api)
- [Integrasi Flutter](#integrasi-flutter)
- [Aturan Presensi](#aturan-presensi)
- [Deployment Production](#deployment-production)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Catatan Pengembangan](#catatan-pengembangan)

## Fitur Utama

### Web Admin

- Dashboard statistik presensi.
- CRUD data siswa, guru, kelas, dan mapel.
- Monitoring data presensi.
- Laporan presensi berdasarkan filter tanggal/kelas.

### Web Guru

- Dashboard guru.
- Buka sesi presensi harian (wali kelas).
- Buka sesi presensi mapel (guru pengampu).
- Generate dan refresh QR token sesi.
- Monitoring kehadiran realtime per sesi.
- Tutup sesi dan auto-generate status tidak hadir untuk siswa yang belum scan.
- Riwayat sesi dan rekap presensi.
- Export rekap ke Excel.

### API Mobile (Siswa)

- Login dengan email atau NIS.
- Ambil profil user saat ini.
- Ganti password.
- Scan QR presensi.
- Lihat riwayat presensi.
- Lihat presensi hari ini.

## Teknologi yang Digunakan

- PHP 8.2+
- Laravel 12
- Laravel Sanctum (token auth API)
- Laravel Livewire + Volt + Flux (UI web)
- MySQL/SQLite
- Vite + Tailwind CSS 4
- Maatwebsite Excel (export laporan)

## Peran Pengguna

- Admin:
    - Kelola master data (siswa/guru/kelas/mapel).
    - Akses dashboard admin dan laporan.
- Guru:
    - Kelola sesi presensi harian/mapel.
    - Monitoring peserta hadir/terlambat/tidak hadir.
    - Lihat riwayat dan export rekap.
- Siswa:
    - Login dari aplikasi mobile.
    - Scan QR saat sesi aktif.
    - Lihat histori presensi pribadi.

Catatan implementasi role:

- Tidak menggunakan Spatie roles.
- Role dicek melalui helper model User: `isAdmin()`, `isGuru()`, `isSiswa()`.

## Alur Singkat Sistem

1. Guru membuka sesi presensi (harian atau mapel).
2. Sistem membuat `session_token` aktif selama sesi berjalan.
3. QR ditampilkan (konten utama: token sesi).
4. Siswa scan QR dari aplikasi mobile dan kirim koordinat lokasi.
5. Sistem validasi:
    - token sesi aktif,
    - role siswa,
    - siswa berada di kelas yang sesuai,
    - belum pernah presensi di sesi tersebut,
    - lokasi siswa dalam radius guru (jika lokasi sesi diaktifkan).
6. Status otomatis:
    - `hadir` jika scan <= 5 menit dari sesi dimulai,
    - `terlambat` jika scan > 5 menit.
7. Saat sesi ditutup, siswa yang belum scan dicatat sebagai `tidak_hadir`.

## Struktur Modul

- `app/Livewire/Admin`: modul admin (dashboard, siswa, guru, kelas, mapel, laporan).
- `app/Livewire/Guru`: modul guru (dashboard, presensi harian, presensi mapel, riwayat).
- `app/Http/Controllers/Api`:
    - `AuthController.php` untuk login/token/profile/password.
    - `PresensiController.php` untuk endpoint presensi guru/siswa/admin.
- `app/Models`:
    - `User`, `Siswa`, `Kelas`, `Mapel`, `Presensi`, `PresensiSession`, `QrSession`.
- `database/seeders`:
    - `SmkDataSeeder` (dataset sekolah),
    - `PresensiSemesterSeeder` (data presensi semester dummy).

## Persyaratan Sistem

- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 18 dan npm
- MySQL 8+ (disarankan untuk pengembangan), atau SQLite
- Ekstensi PHP umum Laravel (mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath)

## Instalasi Lokal

1. Clone repository

```bash
git clone <url-repository> admin-web
cd admin-web
```

2. Install dependency backend dan frontend

```bash
composer install
npm install
```

3. Siapkan file environment

```bash
cp .env.example .env
php artisan key:generate
```

4. Atur koneksi database pada `.env`.

5. Jalankan migrasi dan seeder

```bash
php artisan migrate --seed
```

6. Build asset frontend

```bash
npm run build
```

## Konfigurasi Environment

Contoh konfigurasi utama untuk MySQL:

```env
APP_NAME="Presensi Sekolah"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presensi
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

Jika ingin memakai SQLite:

- set `DB_CONNECTION=sqlite`
- buat file `database/database.sqlite`

## Migrasi dan Seeder

Perintah umum:

```bash
php artisan migrate
php artisan db:seed
```

Reset penuh database:

```bash
php artisan migrate:fresh --seed
```

Seeder utama (`DatabaseSeeder`) menjalankan:

- `SmkDataSeeder`
- `PresensiSemesterSeeder`

## Akun Default Seeder

Setelah `php artisan migrate:fresh --seed`, data contoh yang dibuat antara lain:

- 1 akun admin operator.
- 24 akun guru.
- 12 kelas.
- 120 mapel (10 mapel per kelas).
- 100 siswa + akun user terkait.

Akun admin default:

- Email: `admin@operator.belajar.id`
- Password: `password`

Contoh akun siswa dari seeder:

- Email pola: `siswa{n}@siswa.belajar.id`
- Password: `password`
- NIS pola: `26xxxxxx`

## Menjalankan Aplikasi

### Opsi 1: Jalan terpisah

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

### Opsi 2: Sekali jalan (script composer)

```bash
composer run dev
```

Script ini menjalankan:

- server Laravel,
- queue listener,
- Vite dev server.

## Rute Web

Public:

- `GET /` halaman welcome.

Auth redirect:

- `GET /dashboard` redirect otomatis berdasarkan role:
    - Admin -> `/admin/dashboard`
    - Guru -> `/guru/dashboard`

Admin area (`check.admin`):

- `/admin/dashboard`
- `/admin/siswa`
- `/admin/guru`
- `/admin/kelas`
- `/admin/mapel`
- `/admin/presensi`
- `/admin/laporan`

Guru area (`check.guru`):

- `/guru/dashboard`
- `/guru/presensi`
- `/guru/presensi-mapel`
- `/guru/riwayat`

## Dokumentasi API

Base URL (local):

- `http://localhost:8000/api`

Autentikasi API menggunakan bearer token Sanctum.

Header standar:

```http
Accept: application/json
Authorization: Bearer <token>
```

### 1) Auth

`POST /api/login`

Request body:

```json
{
    "nis_or_email": "26123456",
    "password": "password"
}
```

Catatan:

- Endpoint login API saat ini dibatasi untuk user role `Siswa`.

`GET /api/me`

- Ambil profil user yang sedang login.

`POST /api/logout`

- Hapus token aktif saat ini.

`POST /api/change-password`

Request body:

```json
{
    "old_password": "password-lama",
    "new_password": "password-baru",
    "new_password_confirmation": "password-baru"
}
```

### 2) Endpoint Siswa

`POST /api/siswa/presensi/scan`

Request body:

```json
{
    "qr_data": "SESSION_TOKEN_DARI_QR",
    "lat": -7.250445,
    "lng": 112.768845
}
```

`GET /api/siswa/presensi/history`

- Optional query: `date_from`, `date_to`

`GET /api/siswa/presensi/today`

### 3) Endpoint Guru

`POST /api/guru/sesi`

Request body:

```json
{
    "kelas_id": 1,
    "lat": -7.250445,
    "lng": 112.768845
}
```

`GET /api/guru/sesi/{id}/qr`

- Mendapat payload QR sesi.

`POST /api/guru/sesi/{id}/refresh`

- Regenerate token QR sesi aktif.

`GET /api/guru/sesi/{id}/monitoring`

- Statistik live kehadiran.

`POST /api/guru/sesi/{id}/close`

- Menutup sesi.

### 4) Endpoint Admin

`GET /api/admin/laporan`

Optional query:

- `date_from=YYYY-MM-DD`
- `date_to=YYYY-MM-DD`
- `kelas_id=<id>`

## Integrasi Flutter

Panduan teknis khusus penyesuaian Flutter tersedia di:

- [docs/flutter-integration.md](docs/flutter-integration.md)

Dokumen tersebut mencakup:

- penyesuaian payload login (`nis_or_email`),
- parsing data `siswa_data`,
- pengiriman data scan QR dan lokasi,
- handling `tipe_sesi` (`harian`/`mapel`) dan status presensi,
- normalisasi hasil scan QR (token mentah vs JSON),
- mapping error code ke pesan UI Flutter.

## Aturan Presensi

- Satu siswa hanya bisa presensi sekali untuk satu sesi.
- Validasi kelas: siswa harus berasal dari kelas sesi.
- Radius lokasi diterapkan jika guru membuka sesi dengan koordinat.
- Status hadir otomatis:
    - <= 5 menit: `hadir`
    - > 5 menit: `terlambat`
- Saat sesi ditutup, siswa yang tidak scan diisi `tidak_hadir`.
- Keterangan `tidak_hadir` dapat berupa: `tanpa_keterangan`, `sakit`, `izin`.

## Deployment Production

Panduan ini untuk server Linux (Ubuntu) dengan stack umum: Nginx + PHP-FPM + MySQL + Supervisor.

### 1) Persiapan server

Install paket utama:

```bash
sudo apt update
sudo apt install -y nginx mysql-server unzip git supervisor
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl
```

Install Composer dan Node.js LTS sesuai standar server Anda.

### 2) Deploy source code

```bash
cd /var/www
sudo git clone <url-repository> admin-web
cd admin-web

composer install --no-dev --optimize-autoloader
npm install
npm run build
```

Set permission direktori writable Laravel:

```bash
sudo chown -R www-data:www-data /var/www/admin-web
sudo chmod -R 775 /var/www/admin-web/storage /var/www/admin-web/bootstrap/cache
```

### 3) Konfigurasi environment production

```bash
cp .env.example .env
php artisan key:generate
```

Pastikan nilai minimum production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presensi
DB_USERNAME=presensi_user
DB_PASSWORD=strong_password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Lanjutkan setup database:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Optimasi cache Laravel:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4) Konfigurasi Nginx

Contoh server block (`/etc/nginx/sites-available/presensi`):

```nginx
server {
    listen 80;
    server_name domain-anda.com www.domain-anda.com;

    root /var/www/admin-web/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan konfigurasi:

```bash
sudo ln -s /etc/nginx/sites-available/presensi /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 5) SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

### 6) Queue worker (Supervisor)

Buat file `/etc/supervisor/conf.d/presensi-worker.conf`:

```ini
[program:presensi-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/admin-web/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/admin-web/storage/logs/worker.log
stopwaitsecs=3600
```

Apply Supervisor config:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start presensi-worker:*
```

### 7) Scheduler (Cron)

Tambahkan cron pada user web server:

```bash
* * * * * cd /var/www/admin-web && php artisan schedule:run >> /dev/null 2>&1
```

### 8) Deploy update berikutnya

Setiap ada update kode:

```bash
cd /var/www/admin-web
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart presensi-worker:*
sudo systemctl reload nginx
```

### 9) Opsi Apache (shared hosting/VPS)

Jika menggunakan Apache:

- pastikan document root mengarah ke folder `public`.
- aktifkan `mod_rewrite`.
- jalankan command optimasi Laravel yang sama seperti di atas.

### 10) Checklist hardening

- Gunakan password DB yang kuat dan user DB khusus aplikasi.
- Set `APP_DEBUG=false` di production.
- Batasi permission file hanya yang diperlukan.
- Pastikan backup database terjadwal.
- Monitor log: `storage/logs/laravel.log` dan log worker.
- Gunakan HTTPS penuh untuk endpoint web dan API.

## Testing

Jalankan seluruh test:

```bash
php artisan test
```

Atau gunakan Pest langsung:

```bash
./vendor/bin/pest
```

Catatan:

- Konfigurasi test di `phpunit.xml` saat ini memakai database MySQL (`presensi_test`).
- Pastikan database test tersedia sebelum menjalankan test.

## Troubleshooting

### 401 Unauthenticated di API

- Pastikan header `Authorization: Bearer <token>` terkirim.
- Login ulang untuk mendapatkan token baru.

### 403 saat akses dashboard tertentu

- Cek role user (`Admin`, `Guru`, `Siswa`).
- Web route admin/guru dilindungi middleware `check.admin` dan `check.guru`.

### QR dinyatakan expired/tidak aktif

- Guru harus memastikan sesi masih aktif.
- Jika token sudah berganti, siswa harus scan QR terbaru.

### Asset frontend tidak tampil

- Jalankan `npm run dev` (development) atau `npm run build` (production).

### Konflik data lama setelah update skema

- Gunakan `php artisan migrate:fresh --seed` di environment lokal.

## Catatan Pengembangan

- Registrasi user publik sedang dinonaktifkan.
- Export PDF di modul riwayat guru masih placeholder (belum implementasi final).
- API login menggunakan field `nis_or_email`.

---

Jika Anda ingin, README ini bisa saya lanjutkan dengan:

- contoh koleksi Postman,
- diagram arsitektur database,
- atau panduan deployment (shared hosting/VPS).
