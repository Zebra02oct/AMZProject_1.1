# Dokumentasi Penyesuaian Flutter (Presensi Terbaru)

Dokumen ini menjelaskan perubahan dan kontrak API terbaru agar aplikasi Flutter sinkron dengan backend Presensi.

## 1. Ringkasan Perubahan Penting

- Login mobile wajib kirim field `nis_or_email` (bukan `email` atau `nis` terpisah).
- Endpoint login API saat ini hanya menerima user role `Siswa`.
- Data siswa sekarang dikembalikan pada object `user.siswa_data`.
- Presensi scan menyimpan `tipe_sesi` dari sesi aktif (`harian` atau `mapel`).
- Endpoint scan saat ini membaca `qr_data` sebagai `session_token` mentah.
- Validasi lokasi aktif jika sesi guru menyimpan `latitude` dan `longitude`.

## 2. Base URL dan Environment Flutter

Gunakan base URL sesuai device:

- Android emulator: `http://10.0.2.2:8000/api`
- iOS simulator: `http://127.0.0.1:8000/api`
- Device fisik: `http://10.127.26.131:8000/api`

Contoh `dotenv` Flutter:

```env
API_BASE_URL=http://10.127.26.131:8000/api
```

## 3. Kontrak Auth (Wajib Disesuaikan)

### 3.1 Login

Endpoint: `POST /login`

Request body:

```json
{
    "nis_or_email": "26123456",
    "password": "password"
}
```

Response sukses (ringkas):

```json
{
    "success": true,
    "message": "Login berhasil",
    "user": {
        "id": 10,
        "name": "Siswa 001",
        "email": "siswa1@siswa.belajar.id",
        "role": "Siswa",
        "siswa_data": {
            "id": 1,
            "nis": "26000001",
            "kelas_id": 1
        }
    },
    "token": "1|sanctum_token_xxx",
    "token_type": "Bearer"
}
```

Hal yang harus dilakukan di Flutter:

- Simpan token dari field `token`.
- Simpan profile user dan `siswa_data` untuk kebutuhan UI (nama, NIS, kelas).
- Semua request berikutnya harus memakai header:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

### 3.2 Get Profil User Aktif

Endpoint: `GET /me`

Response `user.siswa_data` memuat tambahan:

- `phone`
- `address`

Gunakan endpoint ini untuk refresh profile di halaman akun.

### 3.3 Ganti Password

Endpoint: `POST /change-password`

Request body:

```json
{
    "old_password": "lama123",
    "new_password": "baru12345",
    "new_password_confirmation": "baru12345"
}
```

## 4. Kontrak Presensi Siswa

### 4.1 Scan QR Presensi

Endpoint: `POST /siswa/presensi/scan`

Request body minimal:

```json
{
    "qr_data": "SESSION_TOKEN_DARI_QR",
    "lat": -7.250445,
    "lng": 112.768845
}
```

Response sukses (ringkas):

```json
{
    "success": true,
    "message": "Presensi berhasil dicatat sebagai: Hadir",
    "data": {
        "id": 1001,
        "siswa_id": 1,
        "session_id": 77,
        "tipe_sesi": "mapel",
        "mapel_id": 15,
        "tanggal": "2026-04-02",
        "waktu_scan": "2026-04-02T01:10:00.000000Z",
        "status": "hadir",
        "session": {
            "id": 77,
            "kelas_id": 1
        }
    }
}
```

Nilai penting untuk UI Flutter:

- `data.status`: `hadir` | `terlambat`
- `data.tipe_sesi`: `harian` | `mapel`
- `data.mapel_id`: null untuk harian, terisi untuk mapel

### 4.2 Riwayat Presensi

Endpoint: `GET /siswa/presensi/history`

Optional query:

- `date_from=YYYY-MM-DD`
- `date_to=YYYY-MM-DD`

Catatan: response berbentuk pagination Laravel (`data.data`, `data.current_page`, dst).

### 4.3 Presensi Hari Ini

Endpoint: `GET /siswa/presensi/today`

Tampilkan list singkat status hari ini (bisa dipakai di dashboard siswa).

## 5. Penanganan QR di Flutter (Penting)

Saat ini backend scan mencari sesi dengan:

- `session_token == qr_data`

Artinya, nilai yang dikirim ke endpoint scan harus token mentah.

Namun payload QR dari beberapa sumber bisa berbeda:

- token mentah (contoh: `aBcD123...`)
- JSON string (contoh: `{"session_id":77,"token":"aBcD123..."}`)

Agar aman, implementasikan normalisasi QR berikut di Flutter sebelum request scan.

```dart
import 'dart:convert';

String normalizeQrData(String rawScannedValue) {
  final value = rawScannedValue.trim();

  if (value.isEmpty) {
    throw Exception('QR kosong');
  }

  // Jika QR berisi JSON, ambil field token.
  if (value.startsWith('{') && value.endsWith('}')) {
    final decoded = jsonDecode(value);
    if (decoded is Map<String, dynamic> && decoded['token'] is String) {
      final token = (decoded['token'] as String).trim();
      if (token.isNotEmpty) return token;
    }
    throw Exception('Format QR JSON tidak valid');
  }

  // Jika bukan JSON, anggap token mentah.
  return value;
}
```

Lalu kirim:

```dart
final qrToken = normalizeQrData(scannedRawValue);

await dio.post(
  '/siswa/presensi/scan',
  data: {
    'qr_data': qrToken,
    'lat': latitude,
    'lng': longitude,
  },
  options: Options(
    headers: {'Authorization': 'Bearer $token'},
  ),
);
```

## 6. Mapping Model Flutter (Disarankan)

### 6.1 User dan SiswaData

```dart
class SiswaData {
  final int id;
  final String nis;
  final int kelasId;
  final String? phone;
  final String? address;

  SiswaData({
    required this.id,
    required this.nis,
    required this.kelasId,
    this.phone,
    this.address,
  });

  factory SiswaData.fromJson(Map<String, dynamic> json) => SiswaData(
        id: json['id'] as int,
        nis: (json['nis'] ?? '').toString(),
        kelasId: json['kelas_id'] as int,
        phone: json['phone']?.toString(),
        address: json['address']?.toString(),
      );
}

class AppUser {
  final int id;
  final String name;
  final String email;
  final String role;
  final SiswaData? siswaData;

  AppUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.siswaData,
  });

  factory AppUser.fromJson(Map<String, dynamic> json) => AppUser(
        id: json['id'] as int,
        name: (json['name'] ?? '').toString(),
        email: (json['email'] ?? '').toString(),
        role: (json['role'] ?? '').toString(),
        siswaData: json['siswa_data'] == null
            ? null
            : SiswaData.fromJson(json['siswa_data'] as Map<String, dynamic>),
      );
}
```

### 6.2 PresensiRecord

```dart
enum PresensiStatus { hadir, terlambat, tidakHadir, unknown }
enum TipeSesi { harian, mapel, unknown }

PresensiStatus parseStatus(String? value) {
  switch ((value ?? '').toLowerCase()) {
    case 'hadir':
      return PresensiStatus.hadir;
    case 'terlambat':
      return PresensiStatus.terlambat;
    case 'tidak_hadir':
      return PresensiStatus.tidakHadir;
    default:
      return PresensiStatus.unknown;
  }
}

TipeSesi parseTipeSesi(String? value) {
  switch ((value ?? '').toLowerCase()) {
    case 'harian':
      return TipeSesi.harian;
    case 'mapel':
      return TipeSesi.mapel;
    default:
      return TipeSesi.unknown;
  }
}
```

## 7. Error Handling yang Harus Ditampilkan di UI

Mapping status code penting:

- `401`: token invalid / belum login.
- `403`: role tidak sesuai, atau di luar radius lokasi.
- `404`: siswa tidak ditemukan atau tidak terdaftar di kelas sesi.
- `409`: siswa sudah presensi pada sesi ini.
- `410`: sesi tidak aktif atau QR expired.
- `422`: validasi gagal (field kurang/invalid).
- `500`: error server.

Saran pesan user-friendly:

- `410`: "Sesi sudah berakhir. Minta guru tampilkan QR terbaru."
- `409`: "Kamu sudah absen pada sesi ini."
- `403` lokasi: "Kamu berada di luar jangkauan lokasi presensi."

## 8. Alur UI Flutter yang Direkomendasikan

1. Login -> simpan token dan user.
2. Home siswa:
    - tampilkan nama, NIS, dan kelas dari `siswa_data`.
    - tampilkan ringkasan dari endpoint `/siswa/presensi/today`.
3. Scan QR:
    - validasi permission kamera + lokasi.
    - normalisasi hasil scan (raw/json -> token).
    - kirim scan request.
    - tampilkan hasil dengan badge status (`hadir`/`terlambat`) dan `tipe_sesi`.
4. Riwayat:
    - ambil `/siswa/presensi/history`.
    - dukung filter tanggal.
5. Profil:
    - ambil `/me` saat refresh halaman.
    - fitur ubah password via `/change-password`.

## 9. Checklist Implementasi Flutter

- [ ] Ubah payload login menjadi `nis_or_email`.
- [ ] Simpan dan gunakan bearer token di semua request protected.
- [ ] Tambahkan parsing `user.siswa_data`.
- [ ] Tambahkan parser `status` dan `tipe_sesi`.
- [ ] Tambahkan normalisasi QR (token mentah / JSON).
- [ ] Kirim `lat` dan `lng` saat scan.
- [ ] Tampilkan error berdasarkan status code (409/410/403/422).
- [ ] Sesuaikan tampilan riwayat dengan data pagination Laravel.

## 10. Catatan Kompatibilitas

- Jika tim backend nantinya mengubah scanner agar menerima JSON QR langsung, kode normalisasi di Flutter tetap aman karena akan tetap mengirim token.
- Untuk saat ini, anggap kontrak final scan adalah:
    - `qr_data`: string token sesi.

---

Jika diinginkan, dokumen ini bisa dilanjutkan menjadi template service siap pakai untuk Flutter (`auth_service.dart`, `presensi_service.dart`, dan interceptor Dio).
