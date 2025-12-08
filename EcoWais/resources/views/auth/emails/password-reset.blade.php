<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #f5f6fa; padding: 20px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header text-center bg-success text-white">
                        <h2>EcoWais</h2>
                    </div>
                    <div class="card-body">
                        <p>Hello,</p>
                        <p>You requested a password reset. Click the button below to reset your password:</p>
                        <div class="text-center my-4">
                            <a href="{{ url('reset-password/'.$token.'?email='.$email) }}" class="btn btn-success btn-lg">Reset Password</a>
                        </div>
                        <p>If you did not request this, you can safely ignore this email.</p>
                        <p>Thanks,<br>EcoWais Team</p>
                    </div>
                    <div class="card-footer text-center text-muted small bg-light">
                        &copy; {{ date('Y') }} EcoWais. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
