<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PresensiSession;
use App\Models\Siswa;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    // Guru mulai presensi
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        PresensiSession::cleanupExpired();

        if (PresensiSession::active()->where('kelas_id', $request->kelas_id)->where('guru_id', Auth::id())->exists()) {
            return response()->json(['error' => 'Sesi masih aktif untuk kelas ini'], 400);
        }

        $session = PresensiSession::create([
            'kelas_id' => $request->kelas_id,
            'guru_id' => Auth::id(),
            'session_token' => Str::random(40),
            'started_at' => now(),
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'session' => $session->only(['id', 'session_token']),
            'message' => 'Sesi presensi dimulai'
        ]);
    }

    // Refresh QR token
    public function refreshQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:presensi_sessions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $session = PresensiSession::findOrFail($request->session_id);

        if ($session->guru_id !== Auth::id() || !$session->is_active) {
            return response()->json(['error' => 'Sesi tidak valid'], 403);
        }

        $session->update(['session_token' => Str::random(40)]);

        return response()->json([
            'success' => true,
            'session_token' => $session->session_token,
            'message' => 'QR token diperbarui'
        ]);
    }

    // Siswa scan QR
    public function scan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
            'siswa_nis' => 'required|string|size:5',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $session = PresensiSession::active()
            ->where('session_token', $request->session_token)
            ->first();

        if (!$session) {
            return response()->json(['error' => 'Sesi QR tidak valid atau expired'], 400);
        }

        $siswa = Siswa::where('nis', $request->siswa_nis)
            ->where('kelas_id', $session->kelas_id)
            ->first();

        if (!$siswa) {
            return response()->json(['error' => 'Siswa tidak ditemukan atau kelas tidak sesuai'], 400);
        }

        // Check duplicate scan in session
        if (Presensi::where('session_id', $session->id)
            ->where('siswa_id', $siswa->id)
            ->exists()
        ) {
            return response()->json(['error' => 'Anda sudah presensi di sesi ini'], 400);
        }

        // Status logic
        $diff = now()->diffInMinutes($session->started_at);
        if ($diff > 15) {
            $session->update(['is_active' => false, 'ended_at' => now()]);
            return response()->json(['error' => 'Sesi sudah ditutup'], 400);
        }

        $status = $diff <= 5 ? 'hadir' : 'terlambat';

        Presensi::create([
            'session_id' => $session->id,
            'siswa_id' => $siswa->id,
            'tanggal' => today(),
            'waktu_scan' => now(),
            'status' => $status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil! Status: ' . ucfirst($status),
            'status' => $status,
        ]);
    }
}