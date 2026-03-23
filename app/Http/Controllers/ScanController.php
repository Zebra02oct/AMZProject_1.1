<?php

namespace App\Http\Controllers;

use App\Models\QrSession;
use App\Models\Presensi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScanController extends Controller
{
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'siswa_nis' => 'required|string|size:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Data tidak valid'], 422);
        }

        $session = QrSession::where('session_id', $request->session_id)
            ->where('active', true)
            ->where('expired_at', '>', now())
            ->with('kelas')
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Sesi QR tidak valid atau sudah expired'], 400);
        }

        $siswa = Siswa::where('nis', $request->siswa_nis)
            ->where('kelas_id', $session->kelas_id)
            ->first();

        if (!$siswa) {
            return response()->json(['error' => 'Siswa tidak ditemukan atau kelas tidak sesuai'], 400);
        }

        // Check if already present today
        $existing = Presensi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', today())
            ->first();

        if ($existing) {
            return response()->json(['error' => 'Anda sudah presensi hari ini'], 400);
        }

        // Status logic: hadir if within first 2 min, else terlambat
        $sessionAge = now()->diffInMinutes($session->started_at);
        $status = $sessionAge <= 2 ? 'hadir' : 'terlambat';

        Presensi::create([
            'siswa_id' => $siswa->id,
            'qr_session_id' => $session->id,
            'tanggal' => today(),
            'waktu' => now(),
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil! Status: ' . ucfirst($status),
            'status' => $status,
        ]);
    }
}
