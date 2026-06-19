<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class PengaturanController extends Controller
{
    public function indexPengaturan(Request $request)
    {
        $searchQuery = $request->query('search', '');
        $userId = session('user_id');

        // JIKA SESSION KOSONG: Ambil data default untuk testing
        if (!$userId) {
            $defaultAdmin = DB::table('users')->where('role', 'admin')->first();
            if ($defaultAdmin) {
                $userId = $defaultAdmin->id_user;
                
                // Cari detail id_admin dari user default
                $adminDetail = DB::table('admins')->where('id_user', $userId)->first();

                session([
                    'user_id'    => $defaultAdmin->id_user,
                    'user_email' => $defaultAdmin->email,
                    'user_type'  => $defaultAdmin->role,
                    'type_id'    => $adminDetail ? (string) $adminDetail->id_admin : null
                ]);
            }
        }

        // 1. Mengambil 5 aktivitas terbaru berdasarkan id_admin (bukan id_user)
        $adminLogs = [];
        
        // Ambil id_admin dari session 'type_id' yang diset saat login
        $idAdmin = session('type_id');

        // Jika session 'type_id' tidak ada, kita bantu cari manual berdasarkan id_user
        if (!$idAdmin && $userId) {
            $adminDetail = DB::table('admins')->where('id_user', $userId)->first();
            if ($adminDetail) {
                $idAdmin = $adminDetail->id_admin;
                session(['type_id' => (string) $idAdmin]);
            }
        }

        if ($idAdmin) {
            $adminLogs = DB::table('admin_logs') 
                ->where('id_admin', $idAdmin) // Diubah dari $userId menjadi $idAdmin sesuai ERD
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        // 2. Menghitung data statistik counter
        $totalPending = DB::table('internships')
            ->where('approval_status', 'pending')
            ->count();

        $totalAccepted = DB::table('internships')
            ->where('approval_status', 'approved')
            ->count();

        return view('pengaturan', compact('adminLogs', 'totalPending', 'totalAccepted', 'searchQuery'));
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'validation_method'   => 'required|in:manual,auto',
            'document_expiration' => 'required|in:7,14,30',
        ]);

        $userId = session('user_id');
        $idAdmin = session('type_id');

        // Logika Ganti Password
        if ($request->filled('current_password')) {
            $user = DB::table('users')->where('id_user', $userId)->first();

            if (!$user || !Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Password saat ini yang Anda masukkan salah.']);
            }

            $request->validate([
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            DB::table('users')
                ->where('id_user', $userId)
                ->update([
                    'password' => Hash::make($request->new_password),
                ]);
        }

        // Catat riwayat perubahan menggunakan helper agar terhindar dari bug id_user/id_admin
        $this->logAdminActivity('Mengubah konfigurasi sistem dan parameter kontrol web');

        return redirect()->back()->with('success', 'Pengaturan akun dan sistem berhasil diperbarui!');
    }

    /**
     * Helper khusus untuk mencatat aktivitas ke tabel admin_logs menggunakan id_admin (UUID)
     */
    private function logAdminActivity($actionDescription)
    {
        $idAdmin = session('type_id'); 

        // Jika tidak ada di session, kita cari manual ke database berdasarkan id_user
        if (!$idAdmin && session('user_id')) {
            $admin = DB::table('admins')->where('id_user', session('user_id'))->first();
            if ($admin) {
                $idAdmin = $admin->id_admin;
                session(['type_id' => (string) $idAdmin]);
            }
        }

        if ($idAdmin) {
            DB::table('admin_logs')->insert([
                'id_admin_log' => (string) Str::uuid(), 
                'id_admin'     => $idAdmin,
                'action'       => $actionDescription,
                'created_at'   => now(),
            ]);
        }
    }

    /**
     * Hapus semua log aktivitas milik admin yang sedang login
     */
    public function clearLogs(Request $request)
    {
        $idAdmin = session('type_id');

        if (!$idAdmin && session('user_id')) {
            $admin = DB::table('admins')->where('id_user', session('user_id'))->first();
            if ($admin) {
                $idAdmin = $admin->id_admin;
                session(['type_id' => (string) $idAdmin]);
            }
        }

        if ($idAdmin) {
            DB::table('admin_logs')->where('id_admin', $idAdmin)->delete();
        }

        return redirect('/pengaturan')->with('success', 'Seluruh riwayat log aktivitas berhasil dihapus.');
    }
}