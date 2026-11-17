<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Day Care Centre</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .login-card {
            max-width: 440px;
            width: 100%;
            border: none;
            border-radius: 15px;
        }

        .logo {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .login-title {
            font-weight: 600;
            color: #174b35;
        }

        .btn-primary {
            background-color: #176b45;
            border-color: #176b45;
        }

        .btn-primary:hover {
            background-color: #105536;
            border-color: #105536;
        }

        .stat-box {
            background: #f4f6f9;
            border-radius: 10px;
            padding: 10px 6px;
            text-align: center;
        }

        .stat-label {
            font-size: 11px;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 700;
            color: #174b35;
            line-height: 1.2;
        }
    </style>
</head>

<body>

<div class="container d-flex align-items-center justify-content-center"
     style="min-height: 100vh;">

    <div class="card shadow login-card">

        <div class="card-body p-4">

            <!-- Logo -->
            <div class="text-center">
                <!--  <img src="{{ asset('images/images.png') }}"
                     alt="Day Care Centre Logo"
                     class="logo"> -->


                <h4 class="login-title mb-1">
                    Day Care Centre
                </h4>

                <p class="text-muted mb-3">
                     Login Portal
                </p>
            </div>

            <!-- Summary Numbers -->


            <!-- Error Message -->
            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
        @endif

        <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control"
                           value="{{ old('email') }}"
                           placeholder="Enter your email"
                           required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="Enter your password"
                           required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Login
                </button>
            </form>

            <hr>

            <p class="text-center text-muted small mb-0">
                
            </p>

        </div>
    </div>
</div>

</body>
</html>
