<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">    
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="background-image">
        <body style="background: url('{{ asset('img/bg6.png') }}') no-repeat center center fixed; background-size: cover;">
    </div>
    <div class="container">
        <div class="card p-4 shadow">
            <div class="row">
                <div class="col" id="imageSection">
                    <img src="{{ asset('img/logo_unscollab2.png') }}" alt="UNSCollab Logo" style="width: 280px;">
                    <div class="card-body">
                        <p class="card-text text-center" style="color: black;">Bridging industry and campus collaboration</p>
                    </div>
                </div>
                <div class="col p-4">
                    <div id="formSection">
                        <!-- Alert Notification -->
                        <div id="alertContainer"></div>

                        <div class="d-flex mb-5" style="gap: 10px;">
                            <button id="companyBtn" class="btn btn-info text-black w-100 mb-2" onclick="setMode('company')">Company</button>
                            <button id="adminBtn" class="btn btn-light w-100 mb-2" onclick="setMode('admin')">Admin</button>
                        </div>
                        <h3 id="titleLogin" class="mb-3" style="color: black;"></h3>
                        
                        <input type="text" id="email" class="form-control mb-3" placeholder="Email">
                        
                        <div class="input-group mb-3">
                            <input type="password" id="password" class="form-control" placeholder="Password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" onclick="togglePasswordVisibility()">
                                <i id="passwordToggleIcon" class="bi bi-eye"></i>
                            </button>
                        </div>

                        <div class="mb-3 text-end" id="forgotPassword">
                            <a href="#" class="text-info text-decoration-none small" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
                        </div>
                        <button id="loginBtn" class="btn btn-info text-black w-100 mb-5">Login</button>
                        <p id="registerText" class="text-center" style="color: black;">
                            Don't have an account? <a href="/register">Register here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordLabel">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="forgotPasswordAlert"></div>
                    <p class="text-muted small mb-3">Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>
                    <input type="email" id="forgotPasswordEmail" class="form-control" placeholder="Masukkan email Anda">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-info text-black" id="sendResetBtn" onclick="handleForgotPassword()">Kirim Link Reset</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
