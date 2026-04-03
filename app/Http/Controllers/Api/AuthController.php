<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'nis_or_email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan email di tabel users,
        // ATAU berdasarkan nis di relasi tabel siswa
        $user = User::with('siswa') // Eager load relasi siswa
            ->where('email', $request->nis_or_email)
            ->orWhereHas('siswa', function ($query) use ($request) {
                $query->where('nis', $request->nis_or_email);
            })
            ->first();

        // Cek apakah user ada dan password cocok
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email/NIS atau Password salah!',
            ], 401);
        }

        // Opsional: Pastikan yang login di mobile app hanya role 'Siswa'
        if ($user->role !== 'Siswa') {
            return response()->json([
                'success' => false,
                'message' => 'Aplikasi ini khusus untuk Siswa.',
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'siswa_data' => $user->siswa ? [
                    'id' => $user->siswa->id,
                    'nis' => $user->siswa->nis,
                    'nama_kelas' => $user->siswa->kelas->Name ?? '-',
                ] : null,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        // Load juga data siswanya saat endpoint /me dipanggil
        $user = User::with('siswa')->find($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Data user berhasil diambil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'siswa_data' => $user->siswa ? [
                    'id' => $user->siswa->id,
                    'nis' => $user->siswa->nis,
                    'kelas_id' => $user->siswa->kelas_id,
                    'phone' => $user->siswa->phone ?? '-',
                    'address' => $user->siswa->address ?? '-',
                    'nama_kelas' => $user->siswa->kelas->name ?? '-',
                ] : null,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    public function changePassword(Request $request)
    {
        // Validasi input dari Flutter
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed', // 'confirmed' otomatis mengecek 'new_password_confirmation'
        ]);

        $user = $request->user();

        // Cek apakah password lama yang dimasukkan cocok dengan di database
        if (! Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password lama tidak sesuai.',
            ], 400); // 400 Bad Request
        }

        // Jika cocok, update dengan password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah.',
        ], 200);
    }
}
