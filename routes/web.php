<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\DaftarCompanyController;
use App\Http\Controllers\PengaturanController;

// ── Auth pages ──
Route::get('/', fn() => view('index'));
Route::get('/register', fn() => view('register'));

// ── Auth actions ──
Route::post('/login', [LoginController::class, 'store']);
Route::post('/register', [RegisterController::class, 'store']);

// FIX #5: Logout harus redirect ke '/', bukan return JSON
Route::post('/logout', function (Request $request) {
    $request->session()->flush();
    return redirect('/');
});

// ── Company Dashboard ──
Route::get('/dashboard', [DashboardController::class, 'index']);

// ── Admin Dashboard ──
// FIX #1: Semua route admin pakai DashboardAdminController, bukan DashboardController
Route::get('/admin-dashboard', [DashboardAdminController::class, 'dashboardAdmin']);
Route::get('/validasi-magang', [DashboardAdminController::class, 'validasiMagang']);
Route::get('/validasi-magang/proses', [DashboardAdminController::class, 'prosesValidasi']);

// FIX: Jaga compat jika ada link ke /dashboard-admin
Route::get('/dashboard-admin', [DashboardAdminController::class, 'dashboardAdmin']);

// ── Company Management ──
// FIX: Pakai DaftarCompanyController untuk daftar perusahaan (ada generate URL Supabase)
Route::get('/daftar-perusahaan', [DaftarCompanyController::class, 'indexAdmin']);
Route::get('/daftar-perusahaan/hapus/{id}', [DaftarCompanyController::class, 'destroy']);
Route::get('/api/perusahaan/{id}', [DaftarCompanyController::class, 'getDetailJson']);

// FIX #2: Daftar Team pakai TeamController (sudah diperbaiki)
Route::get('/daftar-team', [TeamController::class, 'index']);
Route::delete('/daftar-team/hapus/{id}', [TeamController::class, 'destroy']);

// FIX: Route pengaturan pakai PengaturanController agar $adminLogs terkirim ke blade
Route::get('/pengaturan', [PengaturanController::class, 'indexPengaturan']);
Route::post('/pengaturan/simpan', [PengaturanController::class, 'simpan']);
Route::delete('/pengaturan/clear-logs', [PengaturanController::class, 'clearLogs']);

// ── API (Company) ──
Route::get('/api/dashboard', [DashboardController::class, 'getData']);
Route::get('/api/profile', [CompanyController::class, 'getProfile']);
Route::post('/api/profile', [CompanyController::class, 'updateProfile']);
Route::post('/api/profile/update', [CompanyController::class, 'updateProfile']);
Route::post('/api/profile/password', [CompanyController::class, 'updatePassword']);
Route::post('/api/profile/logo', [CompanyController::class, 'updateLogo']);
Route::post('/api/internship/store', [InternshipController::class, 'store']);
Route::post('/api/internship/update', [InternshipController::class, 'update']);
Route::post('/api/internship/delete', [InternshipController::class, 'destroy']);
Route::post('/api/internship/applicant-status', [InternshipController::class, 'updateApplicantStatus']);
Route::post('/api/settings/update', [CompanyController::class, 'updateSettings']);
Route::get('/api/activities', [CompanyController::class, 'getActivities']);

// ── Password reset ──
Route::get('/forgot-password', [PasswordController::class, 'forgotPage']);
Route::post('/forgot-password', [PasswordController::class, 'sendReset']);
Route::get('/reset-password', [PasswordController::class, 'resetPage']);
Route::post('/reset-password', [PasswordController::class, 'doReset']);

// ── Upload Supabase ──
Route::post('/upload-supabase', [UploadController::class, 'uploadToSupabase'])->name('upload.supabase');