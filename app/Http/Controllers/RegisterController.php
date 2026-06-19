<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->json()->all();

        $name     = trim($data['name'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            return response()->json(['success' => false, 'message' => 'Semua field harus diisi'], 400);
        }

        // Cek email sudah ada
        $existing = DB::table('users')->where('email', $email)->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Email sudah terdaftar'], 400);
        }

        // Insert ke tabel users (pakai insert() bukan insertGetId() karena UUID)
        DB::table('users')->insert([
            'id_user'    => DB::raw('gen_random_uuid()'),
            'email'      => $email,
            'password'   => Hash::make($password),
            'role'       => 'company',
            'created_at' => now(),
        ]);

        // Ambil id_user yang baru dibuat
        $newUser = DB::table('users')->where('email', $email)->first();

        // Insert ke tabel companies
        DB::table('companies')->insert([
            'id_company'   => DB::raw('gen_random_uuid()'),
            'id_user'      => $newUser->id_user,
            'company_name' => $name,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Registrasi berhasil!',
            'redirect' => '/dashboard'
        ]);
    }
}