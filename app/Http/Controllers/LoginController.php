<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        // 1. Ambil data input dari form login
        $email    = trim($request->input('email'));
        $password = $request->input('password');
        $mode     = $request->input('mode'); // 'admin' / 'company'

        // 2. Tentukan role berdasarkan mode pilihan login
        $role = ($mode === 'admin') ? 'admin' : 'company';

        // 3. Cari user di database berdasarkan email DAN role
        $user = DB::table('users')
            ->where('email', $email)
            ->where('role', $role)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau hak akses (role) tidak terdaftar.'
            ], 401);
        }

        // 4. Cek password
        $passwordMatches = Hash::check($password, $user->password)
                        || $password === $user->password; // fallback plain text data lama

        if (!$passwordMatches) {
            return response()->json([
                'success' => false,
                'message' => 'Password yang Anda masukkan salah.'
            ], 401);
        }

        // 5. Ambil type_id dan user_name sesuai role
        $typeId   = null;
        $userName = $user->email; // fallback

        if ($user->role === 'admin') {
            // Ambil id_admin + nama dari tabel admins
            $admin = DB::table('admins')->where('id_user', $user->id_user)->first();
            if ($admin) {
                $typeId   = (string) $admin->id_admin;
                $userName = $admin->full_name ?? $user->email;
            }

            // Catat log login admin
            if ($typeId) {
                DB::table('admin_logs')->insert([
                    'id_admin_log' => (string) Str::uuid(),
                    'id_admin'     => $typeId,
                    'action'       => 'Admin Login',
                    'created_at'   => now(),
                ]);
            }

        } else {
            // Ambil id_company + nama perusahaan dari tabel companies
            $company = DB::table('companies')->where('id_user', $user->id_user)->first();
            if ($company) {
                $typeId   = (string) $company->id_company;
                $userName = $company->company_name ?? $user->email;
            }
        }

        // 6. Set session lengkap
        session([
            'user_id'    => $user->id_user,
            'user_email' => $user->email,
            'user_type'  => $user->role,
            'user_name'  => $userName,
            'type_id'    => $typeId,
        ]);

        // 7. Redirect sesuai role
        $redirectUrl = ($user->role === 'admin') ? url('/admin-dashboard') : url('/dashboard');

        return response()->json([
            'success'  => true,
            'message'  => 'Login Berhasil!',
            'redirect' => $redirectUrl
        ]);
    }
}