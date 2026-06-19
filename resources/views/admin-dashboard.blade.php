{{-- Helper format tanggal Indonesia --}}
@php
use Carbon\Carbon;
function formatTanggalIndo($dateString) {
    return Carbon::parse($dateString)->locale('id')->isoFormat('D MMMM YYYY');
}
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>UNSCollab - Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('style.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

    {{-- SIDEBAR --}}
    <div class="sidebar d-none d-lg-block">
        <div class="sidebar-brand">
            <img src="{{ asset('uns-logo.png') }}" alt="Logo" height="45" class="img-fluid">
        </div>

        <div class="nav-label">Menu Utama</div>
        <nav class="d-flex flex-column">
            <a class="nav-link-item active" href="{{ url('/admin-dashboard') }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a>
            <a class="nav-link-item" href="{{ url('/validasi-magang') }}">
                <i class="bi bi-file-earmark-check"></i> Validasi Magang
            </a>
            <a class="nav-link-item" href="{{ url('/daftar-perusahaan') }}">
                <i class="bi bi-buildings"></i> Daftar Perusahaan
            </a>
            <a class="nav-link-item" href="{{ url('/daftar-team') }}">
                <i class="bi bi-people"></i> Daftar Team
            </a>
        </nav>

        <div class="nav-label">Pengaturan</div>
        <nav class="d-flex flex-column">
            <a class="nav-link-item" href="{{ url('/pengaturan') }}">
                <i class="bi bi-gear"></i> Pengaturan
            </a>
        </nav>

        <div class="sidebar-bottom">
            <form action="{{ url('/logout') }}" method="POST" class="d-inline w-100">
                @csrf
                <button type="submit" class="nav-link-item text-danger border-0 bg-transparent w-100" style="text-align: left;">
                    <i class="bi bi-box-arrow-left"></i> Keluar
                </button>
            </form>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <div class="main-content" style="margin-left: 248px; padding: 2rem;">

        <header class="top-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Dashboard Admin</h2>
                <small class="text-muted">Selamat datang, {{ session('user_name') }}</small>
            </div>
            <div class="d-flex align-items-center">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(session('user_name', 'Admin')) }}&background=1FABE1&color=fff"
                     class="rounded-circle" width="45" alt="Profile Admin">
            </div>
        </header>

        {{-- STAT CARDS --}}
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card custom-card p-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Perusahaan</p>
                        <h3 class="fw-bold">{{ $totalCompany }}</h3>
                        <small class="text-success fw-bold"><i class="bi bi-buildings"></i> Mitra Terdaftar</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card p-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Tim Aktif</p>
                        <h3 class="fw-bold">{{ $totalTeam }}</h3>
                        <small class="text-success fw-bold"><i class="bi bi-people"></i> Kelompok Mahasiswa</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card custom-card p-3">
                    <div class="card-body">
                        <p class="text-muted mb-1">Menunggu Validasi</p>
                        <h3 class="fw-bold">{{ $totalPending }}</h3>
                        <small class="text-warning fw-bold"><i class="bi bi-exclamation-circle"></i> Perlu dicek segera</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL PENGAJUAN MAGANG --}}
        <div class="card custom-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold">Pengajuan Magang Terbaru</h5>
                <a href="{{ url('/validasi-magang') }}" class="btn btn-sm btn-outline-primary rounded-3">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Perusahaan</th>
                            <th>Posisi Lowongan</th>
                            <th>Tanggal Masuk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarDokumen as $dokumen)
                            <tr class="border-bottom">
                                <td class="py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="text-white p-2 me-3 d-flex align-items-center justify-content-center fw-bold"
                                             style="width:40px;height:40px;background:#1FABE1;border-radius:10px;">
                                            {{ strtoupper(substr($dokumen->company_name, 0, 1)) }}
                                        </div>
                                        <span class="fw-bold">{{ $dokumen->company_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $dokumen->internship_title }}</td>
                                <td>
                                    @if($dokumen->apply_date)
                                        {{ \Carbon\Carbon::parse($dokumen->apply_date)->locale('id')->isoFormat('D MMM YYYY') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @php $s = strtolower($dokumen->status); @endphp
                                    @if($s === 'pending')
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded">Pending</span>
                                    @elseif($s === 'accepted')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded">Diterima</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded">Ditolak</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ url('/validasi-magang') }}" class="btn btn-sm btn-outline-secondary rounded-3">
                                        <i class="bi bi-eye"></i> Verifikasi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Tidak ada data pengajuan magang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TABEL AKTIVITAS TIM --}}
        <div class="card custom-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold">Aktivitas Tim Mahasiswa</h5>
                <a href="{{ url('/daftar-team') }}" class="btn btn-sm btn-outline-primary rounded-3">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless align-middle">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Kategori</th>
                            <th>Deskripsi Tim</th>
                            <th>Tanggal Buat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($daftarFeedback as $fb)
                            <tr class="border-bottom">
                                <td class="py-3">
                                    <span class="fw-bold d-block">{{ $fb->student_name }}</span>
                                    <small class="text-muted">{{ $fb->NIM }}</small>
                                </td>
                                <td>
                                    <span class="badge px-2 py-1"
                                          style="background:rgba(31,171,225,0.1);color:#1FABE1;border-radius:10px;font-weight:600;">
                                        {{ $fb->category }}
                                    </span>
                                </td>
                                <td class="text-truncate" style="max-width:250px;">{{ $fb->team_description }}</td>
                                <td>{{ \Carbon\Carbon::parse($fb->date_created)->locale('id')->isoFormat('D MMM YYYY') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada tim mahasiswa terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>{{-- end main-content --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>