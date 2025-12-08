<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Bootstrap Only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">

    <div id="login-page" class="page active w-100">

        <div class="login-container mx-auto border rounded shadow-sm p-4" style="max-width: 420px;">

            <div class="login-header text-center mb-3">
                <h2>Login to EcoWais</h2>
                <p>Choose your role to continue</p>
            </div>

            <!-- 🔴 ERROR MESSAGES AREA -->
            @if ($errors->any())
                <div class="error-box mb-3" style="
                    background: #ffe5e5;
                    border-left: 4px solid #ff4d4d;
                    padding: 12px;
                    border-radius: 5px;
                    color: #b30000;
                    font-size: 14px;
                ">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <!-- 🔴 END ERROR DISPLAY -->

            <form id="login-form" action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label>User Type</label>
                    <select name="role" required class="form-select">
                        <option value="">Select Role</option>
                        <option value="barangay_admin">Barangay Admin</option>
                        <option value="barangay_waste_collector">Waste Collection Driver</option>
                        <option value="municipality_administrator">Municipality Administrator</option>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email" class="form-control">
                </div>

                <div class="form-group mb-2">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password" class="form-control">
                </div>

                <p class="text-end mb-3">
                    <a href="{{ route('password.request') }}">Forgot your password?</a>
                </p>

                <button type="submit" class="btn btn-full btn-primary w-100">Login</button>
            </form>

        </div>
    </div>

</body>
</html>
