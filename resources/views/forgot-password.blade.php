<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Password — UNSCollab</title>
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
        <h5 class="mb-1" style="color:black;font-weight:700">Lupa Password?</h5>
        <p class="mb-4" style="color:#666;font-size:13px">Masukkan email dan kami akan kirimkan link reset.</p>
        <input type="email" id="fpEmail" class="form-control mb-3" placeholder="Email terdaftar">
        <button id="fpBtn" class="btn btn-info text-black w-100 mb-3" onclick="handleForgotPassword()">
            Kirim Link Reset
        </button>
        <p class="text-center" style="font-size:13px">
            <a href="/" class="text-info text-decoration-none">← Kembali ke Login</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/script.js') }}"></script>
</body>
</html>