<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Presensi;
use App\Models\QrSession;
use App\Models\Siswa;
use App\Models\Kelas;
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
    private $school_lat = -7.25; // Koordinat Surabaya contoh
    private $school_lng = 112.75;
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
            'kelas_id' => 'required|exists:kelas,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Auth::user();
        if ($user->role !== 'guru') {
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
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'nis' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Validasi lokasi sekolah
        $distance = $this->haversine($request->lat, $request->lng, $this->school_lat, $this->school_lng);
        if ($distance > $this->gps_radius) {
            return response()->json(['error' => 'Lokasi tidak diizinkan (terlalu jauh dari sekolah)'], 403);
        }

        try {
            $qrData = json_decode($request->qr_data, true);
            if (!$qrData || !isset($qrData['session_id'], $qrData['token'])) {
                return response()->json(['error' => 'QR code tidak valid'], 400);
            }

            $session = PresensiSession::where('id', $qrData['session_id'])
                ->where('session_token', $qrData['token'])
                ->where('is_active', true)
                ->first();

            if (!$session) {
                return response()->json(['error' => 'Sesi tidak aktif atau QR expired'], 410);
            }

            $user = Auth::user();
            if ($user->role !== 'siswa') {
                return response()->json(['error' => 'Hanya siswa yang boleh presensi'], 403);
            }

            // Cari siswa by user_id atau NIS
            $siswa = Siswa::where('user_id', $user->id)
                ->orWhere('nis', $request->nis ?? '')
                ->where('kelas_id', $session->kelas_id)
                ->first();

            if (!$siswa) {
                return response()->json(['error' => 'Siswa tidak ditemukan di kelas ini'], 404);
            }

            // Cek sudah presensi belum hari ini di sesi ini
            $exists = Presensi::where('siswa_id', $siswa->id)
                ->where('session_id', $session->id)
                ->first();

            if ($exists) {
                return response()->json(['error' => 'Sudah presensi di sesi ini'], 409);
            }

            DB::beginTransaction();
            $presensi = Presensi::create([
                'siswa_id' => $siswa->id,
                'session_id' => $session->id,
                'qr_session_id' => $session->id, // atau generate separate jika perlu
                'tanggal' => now(),
                'waktu_scan' => now(),
                'waktu' => now()->format('H:i'),
                'status' => 'hadir'
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Presensi berhasil',
                'data' => $presensi->load('siswa', 'session.kelas')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Scan presensi error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal presensi'], 500);
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
        if ($user->role !== 'admin') {
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
