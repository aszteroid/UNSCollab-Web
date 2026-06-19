<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNSCollab - Daftar Perusahaan</title>
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
            <a class="nav-link-item active" href="{{ url('/daftar-perusahaan') }}">
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

    <div class="main-content" style="margin-left: 248px; padding: 2rem;">
        
        <header class="top-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Daftar Perusahaan</h2>
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
                            <h5 class="fw-bold mb-0">Tren Registrasi Mitra</h5>
                            <small class="text-muted">Pertumbuhan pendaftaran akun perusahaan</small>
                        </div>
                        <div class="bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill">
                            <span class="fw-bold">+{{ $compThisMonth }}</span> Bulan Ini
                        </div>
                    </div>
                    <div style="position: relative; height:220px; width:100%;">
                        <canvas id="companiesChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card custom-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Peningkatan Lowongan Magang</h5>
                            <small class="text-muted">Jumlah publikasi projek magang baru</small>
                        </div>
                        <div class="bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                            <span class="fw-bold">+{{ $internThisMonth }}</span> Bulan Ini
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
                <h5 class="fw-bold mb-0">Database Perusahaan atau Mitra</h5>
                
                <div style="max-width: 400px; width: 100%;">
                    <form class="d-flex" role="search" method="GET" action="{{ url('/daftar-perusahaan') }}">
                        <input class="form-control me-2" type="search" name="search" placeholder="Cari nama perusahaan / industri..." aria-label="Search" value="{{ $searchQuery }}">
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
                <table class="table table-borderless align-middle">
                    <thead class="text-muted border-bottom">
                        <tr>
                            <th>Nama Perusahaan</th>
                            <th>Bidang Industri</th>
                            <th>Kontak & Email</th>
                            <th>Bergabung Pada</th>
                            <th class="text-center">Total Lowongan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($daftarPerusahaan) && (is_array($daftarPerusahaan) ? count($daftarPerusahaan) > 0 : $daftarPerusahaan->isNotEmpty()))
                            @foreach ($daftarPerusahaan as $row)
                                <tr class="border-bottom">
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="logo-wrapper" style="width: 40px; height: 40px; min-width: 40px; flex-shrink: 0;">
                                                <div class="bg-custom-blue text-white rounded-3 fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; font-size: 1.1rem;">
                                                    {{ strtoupper(substr($row->company_name, 0, 1)) }}
                                                </div>
                                            </div>

                                            <div class="overflow-hidden ms-3">
                                                <span class="fw-bold d-block text-dark text-truncate" style="max-width: 180px;">{{ $row->company_name }}</span>
                                                <small class="text-muted" title="ID Perusahaan Lengkap: {{ $row->id_company }}">
                                                    ID: #CP-{{ \Illuminate\Support\Str::limit($row->id_company, 8, '...') }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1.5 rounded" style="font-weight: 500;">
                                            {{ $row->industry_field ? $row->industry_field : 'Umum/Lainnya' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="d-block fw-semibold text-dark" style="font-size: 0.9rem;">
                                            <i class="bi bi-telephone me-1 text-muted"></i> {{ $row->contact ? $row->contact : '-' }}
                                        </span>
                                        <small class="text-muted d-block text-truncate" style="max-width: 180px;" title="{{ $row->email }}">
                                            <i class="bi bi-envelope me-1"></i> {{ $row->email }}
                                        </small>
                                    </td>
                                    <td>
                                        <span style="font-size: 0.9rem; white-space: nowrap;">
                                            <i class="bi bi-calendar3 me-1 text-muted"></i> 
                                            {{ $row->create_at ? \Carbon\Carbon::parse($row->create_at)->locale('id')->isoFormat('D MMM YYYY') : '-' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-custom-blue px-3 py-2 rounded-pill fw-bold" style="font-size: 0.85rem;">
                                            {{ $row->total_lowongan }} Program
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-flex flex-column align-items-center justify-content-center p-2 rounded-3 btn-detail-company" 
                                                    style="width: 70px; height: 58px; font-size: 13px; font-weight: 500;"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetailCompany" 
                                                    data-id="{{ $row->id_company }}">
                                                <i class="bi bi-eye mb-0.5" style="font-size: 16px; line-height: 1;"></i> Detail
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">Tidak ada data perusahaan mitra ditemukan.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetailCompany" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-buildings text-custom-blue me-2"></i> Profil Lengkap Mitra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center pb-4 mb-4 border-bottom">
                        <div id="companyAvatarContainer" class="mb-3 mb-sm-0 me-0 me-sm-4"></div>
                        <div class="w-100 overflow-hidden">
                            <h3 class="fw-bold mb-2 text-dark" id="compName">-</h3>
                            <div class="row g-2 text-muted" style="font-size: 0.9rem;">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <i class="bi bi-telephone-fill me-2 text-custom-blue"></i> <span id="compContact">-</span>
                                    </div>
                                    <div>
                                        <i class="bi bi-envelope-fill me-2 text-custom-blue"></i> <span id="compEmail">-</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div>
                                        <i class="bi bi-calendar-check-fill me-2 text-custom-blue"></i> <span class="fw-semibold">Bergabung:</span> <span id="compJoined">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2">Deskripsi Perusahaan</h6>
                        <p class="text-muted" id="compBio" style="font-size: 0.92rem; line-height: 1.6; text-align: justify;">-</p>
                    </div>

                    <div>
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-briefcase text-success me-1"></i> Program Lowongan yang Ditawarkan</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border" style="border-radius: 10px; overflow: hidden;">
                                <thead class="table-light text-secondary" style="font-size: 0.88rem;">
                                    <tr>
                                        <th class="ps-3">Nama Program / Posisi</th>
                                        <th>Lokasi</th>
                                        <th>Deadline</th>
                                        <th class="text-center pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="internshipList" style="font-size: 0.9rem;">
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Memuat program magang...</td>
                                    </tr>
                                </tbody>
                            </table>
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
        const ctxCompany = document.getElementById('companiesChart').getContext('2d');
        new Chart(ctxCompany, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthsCompanies) !!},
                datasets: [{
                    label: 'Perusahaan Registrasi',
                    data: {!! json_encode($countsCompanies) !!},
                    borderColor: '#1FABE1',
                    backgroundColor: 'rgba(31, 171, 225, 0.05)',
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#1FABE1',
                    borderWidth: 3
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

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
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('click', function (event) {
                const button = event.target.closest('.btn-detail-company');
                if (!button) return;
                
                const companyId = button.getAttribute('data-id');
                
                document.getElementById('compName').textContent = 'Memuat...';
                document.getElementById('companyAvatarContainer').innerHTML = '<div class="bg-custom-blue text-center fw-bold rounded-4 d-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px; font-size: 2rem;">-</div>';
                document.getElementById('compContact').textContent = '-';
                document.getElementById('compEmail').textContent = '-';
                document.getElementById('compJoined').textContent = '-';
                document.getElementById('compBio').textContent = '-';
                document.getElementById('internshipList').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-custom-blue me-2"></div>Memuat program magang...</td></tr>';
                
                fetch(`/api/perusahaan/${companyId}`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.error) {
                            alert(data.error);
                            return;
                        }
                        
                        const comp = data.company;
                        const jobs = data.internships;
                        
                        document.getElementById('compName').textContent = comp.company_name;
                        document.getElementById('compContact').textContent = comp.contact || comp.contact_person || comp.phone || '-';
                        document.getElementById('compEmail').textContent = comp.email || (comp.user ? comp.user.email : '-') || '-';
                        document.getElementById('compBio').textContent = comp.bio || comp.description || 'Belum ada deskripsi profil mengenai perusahaan ini.';
                        
                        let rawJoinedDate = comp.create_at || comp.created_at || (comp.user ? comp.user.created_at : null);
                        if (rawJoinedDate) {
                            let joinedDateObj = new Date(rawJoinedDate);
                            document.getElementById('compJoined').textContent = !isNaN(joinedDateObj) 
                                ? joinedDateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) 
                                : rawJoinedDate;
                        } else {
                            document.getElementById('compJoined').textContent = '-';
                        }
                                                                                                     
                        let initial = comp.company_name ? comp.company_name.substring(0, 1).toUpperCase() : '-';
                        document.getElementById('companyAvatarContainer').innerHTML = `
                            <div class="bg-custom-blue text-center text-white fw-bold rounded-4 d-flex align-items-center justify-content-center shadow-sm" 
                                 style="width: 70px; height: 70px; font-size: 2.2rem; min-width: 70px;">
                                ${initial}
                            </div>
                        `;
                                                                                                     
                        let htmlList = '';
                        if(jobs && jobs.length > 0) {
                            jobs.forEach(job => {
                                let status = job.approval_status || 'pending';
                                let statusBadge = '';
                                
                                if(status.toLowerCase() === 'pending') {
                                    statusBadge = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>';
                                } else if(status.toLowerCase() === 'approved' || status.toLowerCase() === 'active') {
                                    statusBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>';
                                } else {
                                    statusBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>';
                                }
                                
                                let titleJob = job.title || 'Posisi Tidak Diketahui';
                                let locationJob = job.location || '-';
                                
                                let formattedDate = '-';
                                if (job.deadline) {
                                    let dateObj = new Date(job.deadline);
                                    formattedDate = !isNaN(dateObj) ? dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : job.deadline;
                                }

                                htmlList += `
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">${titleJob}</td>
                                        <td><i class="bi bi-geo-alt text-muted"></i> ${locationJob}</td>
                                        <td>${formattedDate}</td>
                                        <td class="text-center pe-3">${statusBadge}</td>
                                    </tr>
                                `;
                            });
                        } else {
                            htmlList = '<tr><td colspan="4" class="text-center py-4 text-muted">Perusahaan ini belum pernah menerbitkan lowongan magang.</td></tr>';
                        }
                        
                        document.getElementById('internshipList').innerHTML = htmlList;
                    })
                    .catch(err => {
                        console.error(err);
                        document.getElementById('internshipList').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-danger">Gagal memuat data dari server.</td></tr>';
                    });
            });
        });
    </script>
</body>
</html>