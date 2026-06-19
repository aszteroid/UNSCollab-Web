<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UNSCollab - Pengaturan Admin</title>
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
            <a class="nav-link-item" href="{{ url('/daftar-team') }}">
                <i class="bi bi-people"></i> Daftar Team
            </a>
        </nav>

        <div class="nav-label">Pengaturan</div>
        <nav class="d-flex flex-column">
            <a class="nav-link-item active" href="{{ url('/pengaturan') }}">
                <i class="bi bi-gear"></i> Pengaturan Akun
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
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <header class="top-header mb-4">
            <h2 class="fw-bold mb-1">Pengaturan Kontrol Web</h2>
        </header>

        <div class="d-flex flex-column gap-4">
            
            <div class="card custom-card p-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex align-items-center gap-2 mb-4 text-primary">
                    <i class="bi bi-person-badge fs-5"></i>
                    <h5 class="fw-bold mb-0 text-dark">Data Admin</h5>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Email Akun Utama *</label>
                        <input type="email" class="form-control bg-light text-muted" value="{{ session('user_email') }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-secondary small">Level Akses (Role)</label>
                        <input type="text" class="form-control bg-light text-muted text-uppercase" value="{{ session('user_type', 'admin') }}" readonly>
                    </div>
                </div>

                <small class="text-muted d-block mt-4">
                    <i class="bi bi-info-circle me-1"></i> Email di atas terikat dengan hak akses utama platform dan tidak dapat diganti secara langsung.
                </small>
            </div>

            <div class="card custom-card p-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex align-items-center gap-2 mb-3 text-secondary">
                    <i class="bi bi-clock-history"></i>
                    <h6 class="fw-bold mb-0 text-dark">Aktivitas Terakhir Admin</h6>
                </div>
                
                <div class="position-relative ps-2">
                    @if(isset($adminLogs) && count($adminLogs) > 0)
                        @foreach($adminLogs as $log)
                            <div class="mb-3 border-start border-2 ps-3 pb-2 position-relative">
                                <span class="position-absolute top-0 start-0 translate-middle p-1 rounded-circle style-dot {{ $loop->first ? 'bg-primary' : 'bg-secondary' }}" style="margin-left: -1px;"></span>
                                <div class="fw-bold text-dark small">{{ $log->action }}</div>
                                <small class="text-muted d-block">
                                    {{ \Carbon\Carbon::parse($log->created_at)->locale('id')->diffForHumans() }}
                                </small>
                            </div>
                        @endforeach
                    @else
                        <div class="text-muted small py-2 ps-2">
                            <i class="bi bi-info-circle me-1"></i> Belum ada riwayat aktivitas tercatat.
                        </div>
                    @endif
                </div>                  
            </div>

            <div class="card p-4 border border-danger-subtle bg-danger-subtle bg-opacity-10 rounded-4 shadow-sm">
                <div class="d-flex align-items-center gap-2 mb-2 text-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <h6 class="fw-bold mb-0">Zona Berbahaya</h6>
                </div>
                <p class="text-muted small mb-3">Tindakan pembersihan berikut bersifat permanen dan menghapus seluruh log aktivitas Anda pada sistem.</p>
                
                <div>
                    <button type="button" class="btn btn-danger btn-sm px-4 py-2 rounded-3 fw-semibold text-white d-flex align-items-center gap-2" onclick="hapusLogAktivitas()">
                        <i class="bi bi-trash3"></i> Hapus Semua Riwayat Log
                    </button>
                </div>
            </div>

        </div>

        <form id="clear-logs-form" action="{{ url('/pengaturan/clear-logs') }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function hapusLogAktivitas() {
            if (confirm('Apakah Anda yakin ingin menghapus seluruh riwayat log aktivitas admin?')) {
                document.getElementById('clear-logs-form').submit();
            }
        }
    </script>
</body>
</html>