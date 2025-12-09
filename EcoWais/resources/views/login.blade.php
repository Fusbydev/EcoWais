<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EcoWais</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3a4ba1, #42ad74);
            position: relative;
            overflow: hidden;
        }

        /* Optional background image overlay */
        body::before {
            content: "";
            position: absolute;
            top:0; left:0;
            width: 100%;
            height: 100%;
            background: url('{{ asset("assets/calapan.png") }}') no-repeat center center;
            background-size: cover;
            opacity: 0.15; /* subtle overlay */
            z-index: 0;
        }

        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .login-header h2 {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .login-header p {
            color: #555;
            font-size: 0.95rem;
        }

        .btn-full {
            padding: 0.6rem;
            font-weight: 600;
            font-size: 1rem;
        }

        .error-box {
            font-size: 0.9rem;
        }

        /* Input focus style */
        .form-control:focus {
            border-color: #42ad74;
            box-shadow: 0 0 0 0.25rem rgba(66,173,116,0.25);
        }

        a {
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="login-container text-center">

        <div class="login-header mb-4">
    <img src="{{ asset('assets/log.png') }}" alt="EcoWais Logo" class="mb-2" style="width: 100px; height: 100px;">
    <h2>Login to EcoWais</h2>
    <p>Select your role to continue</p>
</div>


        <!-- Error Messages -->
        @if ($errors->any())
        <div class="error-box mb-3 alert alert-danger text-start">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="login-form" action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-3 text-start">
                <label class="fw-semibold">User Role</label>
                <select name="role" required class="form-select">
                    <option value="">Select Role</option>
                    <option value="barangay_admin">Barangay Admin</option>
                    <option value="barangay_waste_collector">Waste Collection Driver</option>
                    <option value="municipality_administrator">Municipality Administrator</option>
                </select>
            </div>

            <div class="mb-3 text-start">
                <label class="fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope-fill"></i></span>
                    <input type="email" name="email" required placeholder="Enter your email" class="form-control">
                </div>
            </div>

            <div class="mb-3 text-start">
                <label class="fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="password" required placeholder="Enter your password" class="form-control">
                </div>
            </div>

            <div class="mb-3 text-end">
                <a href="{{ route('password.request') }}" class="text-decoration-none small">Forgot your password?</a>
            </div>

            <button type="submit" class="btn btn-primary btn-full w-100 rounded-pill">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
        </form>

        <p class="mt-3 text-muted small">© 2025 EcoWais. All rights reserved.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
