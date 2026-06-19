<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    // Halaman forgot password
    public function forgotPage()
    {
        return view('forgot-password');
    }

    // POST kirim reset link
    public function sendReset(Request $request)
    {
        $data  = $request->json()->all();
        $email = trim($data['email'] ?? '');
        $mode  = trim($data['mode'] ?? 'company');

        if (empty($email)) {
            return response()->json(['success' => false, 'message' => 'Email tidak boleh kosong'], 400);
        }

        $role = $mode === 'admin' ? 'admin' : 'company';

        // Cek di tabel users (schema baru pakai enum role)
        $users = DB::table('users')
            ->where('email', $email)
            ->where('role', $role)
            ->first();

        // Selalu return success agar tidak bocorkan info email terdaftar
        if (!$users) {
            return response()->json([
                'success' => true,
                'message' => 'Jika email terdaftar, link reset telah dikirim ke inbox Anda.'
            ]);
        }

        // Generate token
        $token  = bin2hex(random_bytes(32));
        $expiry = now()->addHour();

        // Simpan token ke tabel password_resets
        // Pastikan tabel ini sudah dibuat via migration:
        // Schema: email VARCHAR(255), token VARCHAR(255), expires_at TIMESTAMPTZ, created_at TIMESTAMPTZ
        DB::table('password_resets')->upsert(
            [
                'email'      => $email,
                'token'      => $token,
                'expires_at' => $expiry,
                'created_at' => now(),
            ],
            ['email'],                              // conflict column
            ['token', 'expires_at', 'created_at']  // columns to update on conflict
        );

        $resetLink = url('/reset-password?token=' . $token . '&email=' . urlencode($email));

        // Di production: kirim via Laravel Mail
        // Mail::to($email)->send(new ResetPasswordMail($resetLink));

        return response()->json([
            'success' => true,
            'message' => 'Link reset password telah dikirim.',
            // Hapus baris reset_link ini di production!
            'reset_link' => $resetLink,
        ]);
    }

    // Halaman reset password
    public function resetPage(Request $request)
    {
        return view('reset-password');
    }

    // POST reset password
    public function doReset(Request $request)
    {
        $data     = $request->json()->all();
        $email    = trim($data['email'] ?? '');
        $token    = trim($data['token'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($token) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'Data tidak lengkap'], 400);
        }

        if (strlen($password) < 6) {
            return response()->json(['success' => false, 'message' => 'Password minimal 6 karakter'], 400);
        }

        // Cek token
        $reset = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $token)
            ->first();

        if (!$reset) {
            return response()->json(['success' => false, 'message' => 'Token tidak valid'], 400);
        }

        if (now()->isAfter($reset->expires_at)) {
            return response()->json(['success' => false, 'message' => 'Token sudah expired, minta reset ulang'], 400);
        }

        // Update password di tabel users
        DB::table('users')
            ->where('email', $email)
            ->update(['password' => Hash::make($password)]);

        // Hapus token setelah dipakai
        DB::table('password_resets')->where('email', $email)->delete();

        return response()->json(['success' => true, 'message' => 'Password berhasil direset! Silakan login.']);
    }
}