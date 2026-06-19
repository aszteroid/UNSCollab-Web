<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register</title>
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
            <div class="row" style="gap: 20px;">
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

                        <h3 class="mb-3" style="color: black;">Register Company</h3>
                        <input type="text" id="name" class="form-control mb-3" placeholder="Company Name">
                        <input type="email" id="email" class="form-control mb-3" placeholder="Email">
                        
                        <div class="input-group mb-3">
                            <input type="password" id="password" class="form-control" placeholder="Password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" onclick="togglePasswordVisibility('password')">
                                <i id="passwordToggleIcon" class="bi bi-eye"></i>
                            </button>
                        </div>

                        <div class="input-group mb-4">
                            <input type="password" id="confirmPassword" class="form-control" placeholder="Confirm Password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirmPassword')">
                                <i id="confirmPasswordToggleIcon" class="bi bi-eye"></i>
                            </button>
                        </div>

                        <button id="signupBtn" class="btn btn-info text-black w-100 mb-5">Sign Up</button>
                        <p id="loginText" class="text-center" style="color: black;">
                            Already have an account? <a href="/">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
