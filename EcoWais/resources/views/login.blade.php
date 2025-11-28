<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div id="login-page" class="page active">
        <div class="login-container">
            <div class="login-header">
                <h2>Login to EcoWais</h2>
                <p>Choose your role to continue</p>
            </div>

            <!-- 🔴 ERROR MESSAGES AREA -->
            @if ($errors->any())
                <div class="error-box" style="
                    background: #ffe5e5;
                    border-left: 4px solid #ff4d4d;
                    padding: 12px;
                    margin-bottom: 15px;
                    border-radius: 5px;
                    color: #b30000;
                    font-size: 14px;
                ">
                    <ul style="margin: 0; padding-left: 18px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <!-- 🔴 END ERROR DISPLAY -->

            <form id="login-form" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>User Type</label>
                    <select name="role" required>
                        <option value="">Select Role</option>
                        <option value="barangay_admin">Barangay Admin</option>
                        <option value="barangay_waste_collector">Waste Collection Driver</option>
                        <option value="municipality_administrator">Municipality Administrator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-full">Login</button>
            </form>

        </div>
    </div>
</body>
</html>
