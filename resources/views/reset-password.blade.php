<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password — UNSCollab</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body style="background: url('{{ asset('img/bg6.png') }}') no-repeat center center fixed; background-size: cover;">
<div class="container">
    <div class="card p-4 shadow" style="max-width:480px;width:100%">
        <div class="text-center mb-4">
            <img src="{{ asset('img/logo_unscollab2.png') }}" style="width:180px">
        </div>
        <div id="alertContainer"></div>
        <h5 class="mb-1" style="color:black;font-weight:700">Reset Password</h5>
        <p class="mb-4" style="color:#666;font-size:13px">Masukkan password baru kamu.</p>

        <div class="input-group mb-3">
            <input type="password" id="newPassword" class="form-control" placeholder="Password Baru">
            <button class="btn btn-outline-secondary" onclick="togglePasswordVisibility('newPassword')">
                <i id="newPasswordToggleIcon" class="bi bi-eye"></i>
            </button>
        </div>
        <div class="input-group mb-4">
            <input type="password" id="confirmPassword" class="form-control" placeholder="Konfirmasi Password">
            <button class="btn btn-outline-secondary" onclick="togglePasswordVisibility('confirmPassword')">
                <i id="confirmPasswordToggleIcon" class="bi bi-eye"></i>
            </button>
        </div>

        <button id="resetBtn" class="btn btn-info text-black w-100 mb-3" onclick="handleResetPassword()">
            Reset Password
        </button>
        <p class="text-center" style="font-size:13px">
            <a href="/" class="text-info text-decoration-none">← Kembali ke Login</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/script.js') }}"></script>
<script>
    async function handleResetPassword() {
        const params   = new URLSearchParams(window.location.search);
        const token    = params.get('token');
        const email    = params.get('email');
        const password = document.getElementById('newPassword').value.trim();
        const confirm  = document.getElementById('confirmPassword').value.trim();

        if (!token || !email) {
            showNotification('Link tidak valid.', 'danger');
            return;
        }
        if (!password || password.length < 6) {
            showNotification('Password minimal 6 karakter.', 'danger');
            return;
        }
        if (password !== confirm) {
            showNotification('Password tidak cocok.', 'danger');
            return;
        }

        const btn = document.getElementById('resetBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';

        try {
            const response = await fetch('/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ token, email, password })
            });
            const data = await response.json();
            if (data.success) {
                showNotification(data.message, 'success');
                setTimeout(() => window.location.href = '/', 2000);
            } else {
                showNotification(data.message, 'danger');
            }
        } catch (e) {
            showNotification('Terjadi kesalahan, coba lagi.', 'danger');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Reset Password';
        }
    }
</script>
</body>
</html>