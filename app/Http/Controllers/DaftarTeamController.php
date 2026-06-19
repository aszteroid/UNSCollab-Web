<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DaftarTeamController extends Controller
{
    public function indexAdmin(Request $request)
    {
        $searchQuery = $request->input('search');

        // 1. Tren Registrasi Team (6 Bulan Terakhir)
        $chartTeams = Team::select(
                DB::raw("TO_CHAR(created_at, 'Mon YYYY') as bulan"),
                DB::raw("COUNT(id_team) as total_reg")
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy(DB::raw("TO_CHAR(created_at, 'Mon YYYY')"), DB::raw("DATE_TRUNC('month', created_at)"))
            ->orderBy(DB::raw("DATE_TRUNC('month', created_at)"), 'asc')
            ->get();

        $monthsTeams = $chartTeams->pluck('bulan')->toArray();
        $countsTeams = $chartTeams->pluck('total_reg')->toArray();

        // 2. Tren Lowongan Magang (6 Bulan Terakhir)
        $chartIntern = DB::table('internships')
            ->select(
                DB::raw("TO_CHAR(deadline, 'Mon YYYY') as bulan"),
                DB::raw("COUNT(id_internship) as total_intern")
            )
            ->where('deadline', '>=', Carbon::now()->subMonths(6))
            ->groupBy(DB::raw("TO_CHAR(deadline, 'Mon YYYY')"), DB::raw("DATE_TRUNC('month', deadline)"))
            ->orderBy(DB::raw("DATE_TRUNC('month', deadline)"), 'asc')
            ->get();

        $monthsIntern = $chartIntern->pluck('bulan')->toArray();
        $countsIntern = $chartIntern->pluck('total_intern')->toArray();

        // 3. Counter statistik bulan ini
        $teamsThisMonth = Team::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)->count();
            
        $internThisMonth = DB::table('internships')
            ->whereMonth('deadline', Carbon::now()->month)
            ->whereYear('deadline', Carbon::now()->year)->count();

        // 4. Query utama data Team
        // 4. Query utama data Team
        $daftarTeams = Team::with('creator')
            ->withCount(['members as total_anggota' => function($query) {
                // FIXED: Menggunakan whereRaw agar string 'accepted' terbungkus petik secara aman di PostgreSQL
                $query->whereRaw("team_members.join_status = 'accepted'"); 
            }])
            ->when($searchQuery, function ($query, $search) {
                return $query->where('team_name', 'Ilike', "%{$search}%")
                             ->orWhere('category', 'Ilike', "%{$search}%");
            })
            ->orderBy('id_team', 'desc')
            ->paginate(10);

        return view('daftarTeam', compact(
            'daftarTeams', 
            'searchQuery', 
            'monthsTeams', 
            'countsTeams', 
            'monthsIntern', 
            'countsIntern',
            'teamsThisMonth', 
            'internThisMonth'
        ));
    }

    public function getDetailTeam($id)
    {
        // Cari team beserta relasi creator (student)
        $team = Team::with('creator')->find($id);

        if (!$team) {
            return response()->json(['error' => 'Kelompok tidak ditemukan.'], 404);
        }

        // Hitung total anggota yang tergabung saat ini (sesuaikan dengan logika DB Anda)
        // Misal jika ada tabel pivot atau kolom counter:
        $totalAnggota = $team->members()->count(); // atau sesuai relasi kelompok Anda

        return response()->json([
            'team' => $team,
            'total_anggota' => $totalAnggota
        ]);
    }
}