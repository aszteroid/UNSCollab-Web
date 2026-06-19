<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    // =========================================================
    // COMPANY DASHBOARD
    // =========================================================

public function index()
    {
        if (!session('user_id')) {
            return redirect('/');
        }
        return view('dashboard');
    }

    public function getData()
    {
        $companyId = session('type_id');

        $totalLowongan = DB::table('internships')
            ->where('id_company', $companyId)->count();

        $pendingLowongan = DB::table('internships')
            ->where('id_company', $companyId)
            ->where('approval_status', 'pending')->count();

        $totalPelamar = DB::table('applications')
            ->join('internships', 'applications.id_internship', '=', 'internships.id_internship')
            ->where('internships.id_company', $companyId)->count();

        $diterimaPelamar = DB::table('applications')
            ->join('internships', 'applications.id_internship', '=', 'internships.id_internship')
            ->where('internships.id_company', $companyId)
            ->where('applications.application_status', 'accepted')->count();

        $lowongan = DB::table('internships')
            ->where('id_company', $companyId)
            ->orderBy('posted_at', 'desc')
            ->get();

        $pelamar = DB::table('applications')
            ->join('internships', 'applications.id_internship', '=', 'internships.id_internship')
            ->join('students', 'applications.id_student', '=', 'students.id_student')
            ->join('users', 'students.id_user', '=', 'users.id_user')
            ->where('internships.id_company', $companyId)
            ->select(
                'applications.id_student',
                'applications.id_internship',
                'applications.application_status',
                'applications.apply_date',
                'applications.cv',
                'applications.cover_letter',
                'students.full_name',
                'students.major',
                'students.nim',
                'students.skill',
                'students.experience',
                'students.bio',
                'students.portofolio',
                'students.profile_picture',
                'users.email',
                'internships.title as posisi'
            )
            ->orderBy('applications.apply_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'stats'   => [
                'total_lowongan'   => $totalLowongan,
                'pending_lowongan' => $pendingLowongan,
                'total_pelamar'    => $totalPelamar,
                'diterima'         => $diterimaPelamar,
            ],
            'lowongan' => $lowongan,
            'pelamar'  => $pelamar,
        ]);
    }

    // =========================================================
    // ADMIN DASHBOARD
    // =========================================================

    public function adminIndex()
    {
        if (!session('user_id') || session('user_type') !== 'admin') {
            return redirect('/');
        }

        $totalCompany = DB::table('companies')->count();
        $totalTeam    = DB::table('teams')->count();
        $totalPending = DB::table('internships')->where('approval_status', 'pending')->count();

        $daftarDokumen = DB::table('applications')
            ->join('internships', 'applications.id_internship', '=', 'internships.id_internship')
            ->join('companies', 'internships.id_company', '=', 'companies.id_company')
            ->select(
                'companies.company_name',
                'internships.title as internship_title',
                'applications.apply_date',
                'applications.application_status as status'
            )
            ->orderBy('applications.apply_date', 'desc')
            ->limit(10)
            ->get();

        // Ambil data tim mahasiswa terbaru untuk tabel aktivitas
        $daftarFeedback = DB::table('teams')
            ->join('students', 'teams.id_creator', '=', 'students.id_student')
            ->select(
                'students.full_name as student_name',
                'students.nim as NIM',
                'teams.category',
                'teams.description as team_description',
                'teams.created_at as date_created'
            )
            ->orderBy('teams.created_at', 'desc')
            ->limit(5)
            ->get();

$daftarTeam = DB::table('teams')
            ->leftJoin('students', 'teams.id_creator', '=', 'students.id_student')
            ->select(
                'teams.*', 
                'students.full_name as student_name', 
                'students.nim as student_nim'
            )
            ->orderBy('teams.created_at', 'desc')
            ->get();
                        
        return view('admin-dashboard', compact(
            'totalCompany',
            'totalTeam',
            'totalPending',
            'daftarDokumen',
            'daftarFeedback',
            'daftarTeam'
        ));
    }

    // =========================================================
    // ADMIN — Validasi Magang
    // =========================================================

    public function validasiMagang(Request $request)
    {
        if (!session('user_id') || session('user_type') !== 'admin') {
            return redirect('/');
        }

        $searchQuery = $request->query('search');

        $totalPending  = DB::table('internships')->where('approval_status', 'pending')->count();
        $totalAccepted = DB::table('internships')->where('approval_status', 'approved')->count();

        $queryInternship = DB::table('internships as i')
            ->join('companies as c', 'i.id_company', '=', 'c.id_company')
            ->select(
                'i.id_internship',
                'c.company_name',
                'i.title as internship_title',
                'i.deadline',
                'i.approval_status',
                'i.supporting_document',
                'i.location',
                'c.industry_field'
            );

        if (!empty($searchQuery)) {
            $queryInternship->where(function ($q) use ($searchQuery) {
                $q->where('c.company_name', 'ILIKE', '%' . $searchQuery . '%')
                  ->orWhere('i.title', 'ILIKE', '%' . $searchQuery . '%');
            });
        }

        $daftarLowongan = $queryInternship->orderBy('i.id_internship', 'desc')->get();

        return view('validasiMagang', compact(
            'totalPending',
            'totalAccepted',
            'daftarLowongan',
            'searchQuery'
        ));
    }

    // =========================================================
    // ADMIN — Proses Validasi (Approve / Reject)
    // =========================================================

    public function prosesValidasi(Request $request)
    {
        if (!session('user_id') || session('user_type') !== 'admin') {
            return redirect('/');
        }

        $action = $request->query('action');
        $id     = $request->query('id');

        if (!$id) {
            return redirect()->back()->with('error', 'ID Lowongan tidak ditemukan.');
        }

        if ($action === 'approve') {
            DB::table('internships')
                ->where('id_internship', $id)
                ->update(['approval_status' => 'approved']);

            return redirect()->back()->with('success', 'Lowongan berhasil disetujui dan diterbitkan!');

        } elseif ($action === 'reject') {
            DB::table('internships')
                ->where('id_internship', $id)
                ->update(['approval_status' => 'rejected']);

            return redirect()->back()->with('success', 'Lowongan kerja resmi ditolak.');
        }

        return redirect()->back()->with('error', 'Aksi tidak valid.');
    }
}