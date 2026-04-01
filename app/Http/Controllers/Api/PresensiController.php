<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class PresensiController extends Controller
{
    private $school_lat = -1.273709; // Koordinat Surabaya contoh
    private $school_lng = 1.273709;
    private $gps_radius = 0.075; // 75m dalam km

    /**
     * Hitung jarak Haversine untuk validasi lokasi
     */
    private function haversine($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }

    // ===================================== GURU ENDPOINTS =====================================

    /**
     * Guru mulai sesi presensi baru
     * POST /api/guru/sesi { kelas_id }
     */
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelas_id' => 'required|exists:kelas,id',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Auth::user();
        if (!$user || !$user->isGuru()) {
            return response()->json(['error' => 'Hanya guru yang boleh mulai sesi'], 403);
        }

        // Cek apakah ada sesi aktif
        $activeSession = PresensiSession::guruActive($user->id)->first();
        if ($activeSession) {
            return response()->json([
                'error' => 'Sesi masih aktif',
                'session' => $activeSession
            ], 409);
        }

        DB::beginTransaction();
        try {
            $session = PresensiSession::create([
                'kelas_id' => $request->kelas_id,
                'guru_id' => $user->id,
                'session_token' => Str::random(32),
                'latitude' => $request->lat,
                'longitude' => $request->lng,
                'is_active' => true,
                'started_at' => now()
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Sesi presensi dimulai',
                'session' => $session->load('kelas')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal start sesi: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memulai sesi'], 500);
        }
    }

    /**
     * Get QR code untuk sesi
     * GET /api/guru/sesi/{id}/qr
     */
    public function qr($id)
    {
        $session = PresensiSession::with('kelas')->findOrFail($id);
        $user = Auth::user();

        if ($session->guru_id !== $user->id || !$session->is_active) {
            return response()->json(['error' => 'Sesi tidak valid atau tidak aktif'], 403);
        }

        // Generate QR content (token + session_id)
        $qrContent = json_encode([
            'session_id' => $session->id,
            'token' => $session->session_token,
            'kelas_id' => $session->kelas_id,
            'timestamp' => now()->timestamp
        ]);

        return response()->json([
            'success' => true,
            'session' => $session,
            'qr_content' => $qrContent,
            'qr_url' => null // Generate di frontend dengan qrcode.min.js
        ]);
    }

    /**
     * Refresh QR sesi
     * POST /api/guru/sesi/{id}/refresh
     */
    public function refreshQr($id)
    {
        $session = PresensiSession::findOrFail($id);
        $user = Auth::user();

        if ($session->guru_id !== $user->id || !$session->is_active) {
            return response()->json(['error' => 'Tidak diizinkan'], 403);
        }

        $session->update(['session_token' => Str::random(32)]);
        return response()->json(['success' => true, 'message' => 'QR direfresh']);
    }

    /**
     * Monitoring live presensi
     * GET /api/guru/sesi/{id}/monitoring
     */
    public function monitoring($id)
    {
        $session = PresensiSession::with(['presensis.siswa', 'kelas'])->findOrFail($id);
        $user = Auth::user();

        if ($session->guru_id !== $user->id || !$session->is_active) {
            return response()->json(['error' => 'Tidak diizinkan'], 403);
        }

        $hadir = $session->presensis()->hadir()->count();
        $totalSiswa = $session->kelas->siswas->count();

        return response()->json([
            'success' => true,
            'session' => $session,
            'stats' => [
                'hadir' => $hadir,
                'total' => $totalSiswa,
                'persentase' => $totalSiswa ? round(($hadir / $totalSiswa) * 100, 1) : 0
            ],
            'presensi_list' => $session->presensis()->with('siswa')->latest()->take(20)->get()
        ]);
    }

    /**
     * Tutup sesi presensi
     * POST /api/guru/sesi/{id}/close
     */
    public function close($id)
    {
        $session = PresensiSession::findOrFail($id);
        $user = Auth::user();

        if ($session->guru_id !== $user->id) {
            return response()->json(['error' => 'Tidak diizinkan'], 403);
        }

        $session->update([
            'is_active' => false,
            'ended_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Sesi ditutup']);
    }

    // ===================================== SISWA ENDPOINTS =====================================

    /**
     * Siswa scan QR presensi
     * POST /api/siswa/presensi/scan
     * Body: { qr_data (json), nis?, lat, lng }
     */
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'nis' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            // 1. Langsung cari sesi berdasarkan raw string token dari QR
            $session = PresensiSession::where('session_token', $request->qr_data)
                ->where('is_active', true)
                ->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi presensi tidak aktif atau QR code sudah expired'
                ], 410);
            }

            // 2. VALIDASI LOKASI DINAMIS (Berdasarkan lokasi laptop guru saat buka sesi)
            // Cek apakah guru mengizinkan lokasi saat buka sesi
            if ($session->latitude && $session->longitude) {
                $distance = $this->haversine($request->lat, $request->lng, $session->latitude, $session->longitude);

                // Radius 50 meter (0.05 km)
                if ($distance > 0.05) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal presensi: Kamu berada di luar jangkauan area guru (' . round($distance * 1000) . ' meter).'
                    ], 403);
                }
            }

            $user = Auth::user();
            if (!$user || !$user->isSiswa()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya siswa yang diperbolehkan melakukan presensi'
                ], 403);
            }

            // 2. Cari siswa berdasarkan user_id dan pastikan dia di kelas yang benar
            $siswa = Siswa::where('user_id', $user->id)
                ->where('kelas_id', $session->kelas_id)
                ->first();

            if (!$siswa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kamu tidak terdaftar di kelas untuk sesi presensi ini'
                ], 404);
            }

            // 3. Cek apakah sudah presensi di sesi ini
            $exists = Presensi::where('siswa_id', $siswa->id)
                ->where('session_id', $session->id)
                ->first();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kamu sudah melakukan presensi di sesi ini'
                ], 409);
            }

            // 4. Penentuan Status Hadir / Terlambat (Batas 5 menit / 300 detik)
            $waktuScan = now();
            $scanSeconds = $waktuScan->diffInSeconds($session->started_at);
            $status = $scanSeconds <= 300 ? 'hadir' : 'terlambat';

            DB::beginTransaction();
            $presensi = Presensi::create([
                'siswa_id' => $siswa->id,
                'session_id' => $session->id,
                'qr_session_id' => null,
                'tipe_sesi' => $session->tipe_sesi ?? 'harian',
                'mapel_id' => $session->mapel_id,
                'tanggal' => $waktuScan->toDateString(),
                'waktu_scan' => $waktuScan,
                'waktu' => $waktuScan->format('H:i'),
                'status' => $status
            ]);
            DB::commit();

            // 5. Response Sukses
            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil dicatat sebagai: ' . ucfirst($status),
                'data' => $presensi->load('siswa', 'session.kelas')
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Scan presensi error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan presensi sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Riwayat presensi siswa
     * GET /api/siswa/presensi/history
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['error' => 'Siswa tidak ditemukan'], 404);
        }

        $query = Presensi::with(['session.kelas', 'session.guru'])
            ->where('siswa_id', $siswa->id);

        if ($request->date_from) {
            $query->dateRange($request->date_from, $request->date_to ?? now()->format('Y-m-d'));
        }

        $presensi = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $presensi
        ]);
    }

    /**
     * Presensi hari ini siswa
     * GET /api/siswa/presensi/today
     */
    public function today()
    {
        $user = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->first();

        if (!$siswa) {
            return response()->json(['error' => 'Siswa tidak ditemukan'], 404);
        }

        $todayPresensi = Presensi::with(['session.kelas'])
            ->where('siswa_id', $siswa->id)
            ->today()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $todayPresensi
        ]);
    }

    // ===================================== ADMIN ENDPOINTS =====================================

    /**
     * Laporan presensi admin
     * GET /api/admin/laporan?date_from=&date_to=&kelas_id=
     */
    public function laporan(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['error' => 'Hanya admin'], 403);
        }

        $query = Presensi::with(['siswa.kelas', 'session.guru', 'session.kelas'])
            ->hadir();

        $query->dateRange($request->date_from, $request->date_to);
        $query->kelasFilter($request->kelas_id);

        $total = $query->count();
        $data = $query->latest()->paginate(50);

        return response()->json([
            'success' => true,
            'stats' => [
                'total_hadir' => $total,
                'periode' => $request->date_from . ' s/d ' . ($request->date_to ?? now()->format('Y-m-d'))
            ],
            'data' => $data
        ]);
    }
}
