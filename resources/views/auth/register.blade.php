<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Day Care</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container d-flex align-items-center justify-content-center" style="min-height:100vh;">
    <div class="card shadow-sm" style="max-width: 420px; width:100%;">
        <div class="card-body p-4">
            <h4 class="mb-3 text-center">Employee Registration</h4>
            <form method="POST" action="{{ route("register") }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old("name") }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old("email") }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3 mb-0"><a href="{{ route("login") }}">Already have an account? Login</a></p>
            <p class="small text-muted mt-2 mb-0">Note: an admin will link your account to an employee record so your children appear on your dashboard.</p>
        </div>
    </div>
</div>
</body>
</html>
