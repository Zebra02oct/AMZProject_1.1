# Laravel API for Flutter Presensi - TODO List

Status: ✅ Plan Approved - Implementation Started

## Progress: 3/5 ✅

## 1. Database Schema (Priority 1)

- [ ] Add migration `php artisan make:migration add_user_id_to_siswa_table`
    - $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
- [ ] Run `php artisan migrate`

## 2. Authentication Fix (Priority 1)

- [ ] Edit `app/Http/Controllers/Api/AuthController.php`: Accept 'nis' for login
- [ ] Test login with nis=12345

## 3. Test Data (Priority 1)

- [ ] Create `database/seeders/TestUserSeeder.php`
    - Test siswa: nis=12345, pass=123456, role='siswa', create linked Siswa
- [ ] `php artisan db:seed --class=TestUserSeeder`
- [ ] Update DatabaseSeeder to call it

## 4. Relations (Priority 2)

- [ ] Edit app/Models/User.php: add `public function siswa() { return $this->hasOne(Siswa::class); }`
- [ ] Edit app/Models/Siswa.php: add `public function user() { return $this->belongsTo(User::class); }`

## 5. Config & Server (Priority 2)

- [ ] Edit `.env` SANCTUM_STATEFUL_DOMAINS add 10.0.2.2,192.168.8.155
- [ ] `php artisan config:cache`
- [ ] `php artisan serve --host=0.0.0.0 --port=8000`
- [ ] Test endpoints

## Test Commands

```
curl -X POST http://localhost:8000/api/login -H 'Content-Type: application/json' -d '{\"nis\":\"12345\",\"password\":\"123456\"}'
```

## Completion

- [ ] Flutter connects successfully
