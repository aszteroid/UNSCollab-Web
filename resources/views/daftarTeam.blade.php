<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNSCollab - Daftar Team</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.0/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('style.css') }}" />      
</head>
<body>

    <div class="sidebar d-none d-lg-block">
        <div class="sidebar-brand">
            <img src="{{ asset('uns-logo.png') }}" alt="Logo" height="45" class="img-fluid">
        </div>

        <div class="nav-label">Menu Utama</div>
        <nav class="d-flex flex-column">
            <a class="nav-link-item" href="{{ url('/dashboard-admin') }}">
                <i class="bi bi-grid-1x2"></i> Dashboard
            </a> 
            <a class="nav-link-item" href="{{ url('/validasi-magang') }}">
                <i class="bi bi-file-earmark-check"></i> Validasi Magang
            </a>
            <a class="nav-link-item" href="{{ url('/daftar-perusahaan') }}">
                <i class="bi bi-buildings"></i> Daftar Perusahaan
            </a>
            <a class="nav-link-item active" href="{{ url('/daftar-team') }}">
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

    <div class="main-content" style="margin-left: 248px; padding: 2rem;">
        
        <header class="top-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Daftar Team</h2>
            </div>
            <div class="d-flex align-items-center">
                <a href="#">
                    <img src="https://ui-avatars.com/api/?name=Admin+Zahra&background=1FABE1&color=fff" class="rounded-circle" width="45" alt="Profile Admin">
                </a>
            </div>
        </header>

        <div class="row g-4 mb-5">
            <div class="col-xl-6">
                <div class="card custom-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Tren Pembentukan Team</h5>
                            <small class="text-muted">Pertumbuhan pembuatan Team baru oleh mahasiswa</small>
                        </div>
                        <div class="bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                            <span class="fw-bold">+{{ $teamsThisMonth }}</span> Bulan Ini
                        </div>
                    </div>
                    <div style="position: relative; height:220px; width:100%;">
                        <canvas id="teamsChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card custom-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Peningkatan Team</h5>
                            <small class="text-muted">Jumlah publikasi team baru</small>
                        </div>
                        <div class="bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                            <span class="fw-bold">+{{ $teamsThisMonth }}</span> Bulan Ini
                        </div>
                    </div>
                    <div style="position: relative; height:220px; width:100%;">
                        <canvas id="internshipChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card custom-card p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <h5 class="fw-bold mb-0">Database Team</h5>
                
                <div style="max-width: 400px; width: 100%;">
                    <form class="d-flex" role="search" method="GET" action="{{ url('/daftar-team') }}">
                        <input class="form-control me-2" type="search" name="search" placeholder="Cari nama / kategori team..." aria-label="Search" value="{{ $searchQuery }}">
                        <button class="button-kustom btn-sm me-1 btn-custom-blue" type="submit">Cari</button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-borderless align-middle mb-0">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Nama Team</th>
                            <th>Kategori Team</th>
                            <th>Ketua (Leader)</th>
                            <th class="text-center">Kapasitas Anggota</th>
                            <th>Batas Registrasi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($daftarTeams) > 0)
                            @foreach ($daftarTeams as $row)
                                @php
                                    $cleanName = strip_tags($row->team_name);
                                    if (str_contains($cleanName, 'Log') || str_contains($cleanName, 'alt=')) {
                                        $cleanName = preg_replace('/.*alt=["\']?Log[o]?["\']?\s*/i', '', $cleanName);
                                        $cleanName = preg_replace('/.*src=["\']?[^"\']*["\']?\s*/i', '', $cleanName);
                                        $cleanName = trim($cleanName, ' ="\'><_/-');
                                    }

                                    if (empty($cleanName) || $cleanName == "Log" || strlen($cleanName) < 2) {
                                        if (str_contains($row->team_name, 'Kelompok 5')) $cleanName = 'Kelompok 5';
                                        elseif (str_contains($row->team_name, 'Data Wizards')) $cleanName = 'Data Wizards';
                                        elseif (str_contains($row->team_name, 'Artelegi')) $cleanName = 'Artelegi';
                                        elseif (str_contains($row->team_name, 'Bismillah Gemastik')) $cleanName = 'Bismillah Gemastik';
                                        elseif (str_contains($row->team_name, 'Concer')) $cleanName = 'Concer';
                                        elseif (str_contains($row->team_name, 'Tech Innovators')) $cleanName = 'Tech Innovators';
                                        else $cleanName = "Team #" . substr($row->id_team, 0, 4);
                                    }

                                    $initial = strtoupper(substr($cleanName, 0, 1));
                                @endphp
                                <tr class="border-bottom">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="logo-wrapper me-3">
                                                <div class="bg-custom-blue text-white rounded-3 fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                                    {{ $initial }}
                                                </div>
                                            </div>
                                            <div>
                                                <span class="fw-bold d-block text-dark mb-0" style="font-size: 14.5px;">{{ $cleanName }}</span>
                                                <small class="text-muted">ID: #TM-{{ substr($row->id_team, 0, 8) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1.5 rounded" style="font-weight: 500;">
                                            {{ $row->category ? $row->category : 'Umum/Lainnya' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="d-block fw-semibold text-dark" style="font-size: 14px;">{{ $row->creator->full_name ?? 'Tidak Diketahui' }}</span>
                                        <small class="text-muted">NIM: {{ $row->creator->nim ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge px-3 py-2 rounded-pill fw-bold text-white bg-custom-blue">
                                            {{ $row->total_anggota ?? 1 }} / {{ $row->max_member ?? '∞' }} Anggota
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark" style="font-size: 14px; font-weight: 500;">
                                            <i class="bi bi-calendar-event me-1 text-muted"></i> 
                                            {{ $row->deadline ? \Carbon\Carbon::parse($row->deadline)->locale('id')->isoFormat('D MMM YYYY') : 'Tanpa Tenggat' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- GANTI TOMBOL SEBELUMNYA DENGAN INI -->
                                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex flex-column align-items-center justify-content-center p-2 rounded-3 btn-detail-team" 
                                                style="width: 70px; height: 58px; font-size: 13px; font-weight: 500;"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDetailTeam" 
                                                data-name="{{ $cleanName }}"
                                                data-category="{{ $row->category ?? 'Umum/Lainnya' }}"
                                                data-tag="{{ $row->tag ?? 'NoTag' }}"
                                                data-leader="{{ $row->creator->full_name ?? 'Tidak Diketahui' }} ({{ $row->creator->nim ?? '-' }})"
                                                data-deadline="{{ $row->deadline ? \Carbon\Carbon::parse($row->deadline)->locale('id')->isoFormat('D MMM YYYY') : 'Tanpa Tenggat' }}"
                                                data-description="{{ $row->description ?? 'Team ini belum memasukkan detail ringkasan deskripsi projek.' }}"
                                                data-requirement="{{ $row->requirement ?? 'Tidak ada kualifikasi khusus yang dipersyaratkan oleh Team ini.' }}"
                                                data-max="{{ $row->max_member ? $row->max_member . ' Orang' : '∞ (Tak Terbatas)' }}"
                                                data-current="{{ $row->total_anggota ?? 1 }} Anggota Terdaftar"
                                                data-initial-fallback="{{ $initial }}">
                                            <i class="bi bi-eye mb-0.5" style="font-size: 16px; line-height: 1;"></i> Detail
                                        </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-people text-muted d-block mb-2" style="font-size: 2rem;"></i>
                                    Tidak ada data Team ditemukan.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            @if (count($daftarTeams) > 0)
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 pt-4 border-top mt-3">
                    <div class="text-muted" style="font-size: 14px;">
                        Menampilkan <span class="fw-semibold text-dark">{{ $daftarTeams->firstItem() }}</span> 
                        sampai <span class="fw-semibold text-dark">{{ $daftarTeams->lastItem() }}</span> 
                        dari <span class="fw-semibold text-dark">{{ $daftarTeams->total() }}</span> total Team
                    </div>
                    
                    <div class="custom-pagination">
                        <ul class="pagination mb-0">
                            @if ($daftarTeams->onFirstPage())
                                <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
                            @else
                                <li class="page-item"><a class="page-link" href="{{ $daftarTeams->appends(['search' => $searchQuery])->previousPageUrl() }}" rel="prev">&lsaquo;</a></li>
                            @endif

                            @foreach ($daftarTeams->getUrlRange(1, $daftarTeams->lastPage()) as $page => $url)
                                <li class="page-item {{ $page == $daftarTeams->currentPage() ? 'active' : '' }}">
                                    <a class="page-link" href="{{ $url . (str_contains($url, '?') ? '&' : '?') . http_build_query(['search' => $searchQuery]) }}">{{ $page }}</a>
                                </li>
                            @endforeach

                            @if ($daftarTeams->hasMorePages())
                                <li class="page-item"><a class="page-link" href="{{ $daftarTeams->appends(['search' => $searchQuery])->nextPageUrl() }}" rel="next">&rsaquo;</a></li>
                            @else
                                <li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="modalDetailTeam" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-people text-custom-blue me-2"></i> Detail Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center pb-4 mb-4 border-bottom">
                        <div class="me-3 mb-3 mb-sm-0">
                            <div id="modalTeamInitialView" class="bg-custom-blue text-white rounded-3 fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 65px; height: 65px; font-size: 1.8rem;">
                                -
                            </div>
                        </div>
                        
                        <div class="w-100 overflow-hidden">
                            <h3 class="fw-bold mb-1 text-dark" id="teamNameView">-</h3>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-light text-dark border px-2 py-1" id="teamCategoryView">-</span>
                            </div>
                            <div class="text-muted" style="font-size: 0.88rem;">
                                <i class="bi bi-person-badge-fill me-1 text-custom-blue"></i> Ketua: <span id="teamLeaderView" class="fw-semibold text-dark">-</span> 
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-earmark-text text-custom-blue me-1"></i> Spesifikasi & Deskripsi Projek</h6>
                        <p class="text-muted" id="teamDescriptionView" style="font-size: 0.92rem; line-height: 1.6; text-align: justify;">-</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-patch-check text-success me-1"></i> Syarat & Kualifikasi Kebutuhan</h6>
                        <div class="p-3 bg-light rounded-3 text-secondary" id="teamRequirementView" style="font-size: 0.92rem; white-space: pre-line; line-height: 1.5;">
                            -
                        </div>
                    </div>

                    <div>
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-plus text-primary me-1"></i> Status Slot Anggota</h6>
                        <div class="card p-3 bg-light border-0">
                            <div class="row text-center">
                                <div class="col-6 border-end">
                                    <small class="text-muted d-block">Maksimal Kuota</small>
                                    <span class="fs-5 fw-bold text-dark" id="teamMaxMemberView">-</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Terisi Saat Ini</small>
                                    <span class="fs-5 fw-bold text-custom-blue" id="teamCurrentMemberView">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-secondary px-4 py-2 fw-semibold" style="border-radius: 10px;" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. GRAFIK REGISTRASI TEAM (LINE CHART)
        const ctxTeam = document.getElementById('teamsChart').getContext('2d');
        new Chart(ctxTeam, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthsTeams) !!},
                datasets: [{
                    label: 'Team Baru Dibentuk',
                    data: {!! json_encode($countsTeams) !!},
                    borderColor: '#1FABE1',
                    backgroundColor: 'rgba(31, 171, 225, 0.05)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#1FABE1',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { drawBorder: false }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. GRAFIK KENAIKAN INTERNSHIP (BAR CHART)
        const ctxIntern = document.getElementById('internshipChart').getContext('2d');
        new Chart(ctxIntern, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthsIntern) !!},
                datasets: [{
                    label: 'Lowongan Diupload',
                    data: {!! json_encode($countsIntern) !!},
                    backgroundColor: '#1cc88a',
                    borderRadius: 6,
                    barThickness: 20
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { drawBorder: false }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });

        // EVENT LISTENER MODAL DETAIL TEAM
        const modalDetailTeam = document.getElementById('modalDetailTeam');
        if (modalDetailTeam) {
            modalDetailTeam.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                const name            = button.getAttribute('data-name') || '';
                const category        = button.getAttribute('data-category') || '-';
                const leader          = button.getAttribute('data-leader') || '-';
                const description     = button.getAttribute('data-description') || '-';
                const requirement     = button.getAttribute('data-requirement') || '-';
                const maxMember       = button.getAttribute('data-max') || '-';
                const currentMember   = button.getAttribute('data-current') || '-';
                const fallbackInitial = button.getAttribute('data-initial-fallback') || '?';

                const displayName   = name.trim() || ('Team #' + fallbackInitial);
                const initialLetter = displayName.charAt(0).toUpperCase() || fallbackInitial;

                document.getElementById('teamNameView').textContent         = displayName;
                document.getElementById('modalTeamInitialView').textContent = initialLetter;
                document.getElementById('teamCategoryView').textContent     = category;
                document.getElementById('teamLeaderView').textContent       = leader;
                document.getElementById('teamDescriptionView').textContent  = description;
                document.getElementById('teamRequirementView').textContent  = requirement;
                document.getElementById('teamMaxMemberView').textContent    = maxMember;
                document.getElementById('teamCurrentMemberView').textContent = currentMember;
            });
        }
    });
    </script>
</body>
</html>