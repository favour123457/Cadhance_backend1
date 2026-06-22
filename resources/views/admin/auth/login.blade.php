<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <title>Admin Login | {{ config('app.name') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('') }}src/assets/img/favicon.ico">
    <link href="{{ asset('') }}layouts/vertical-dark-menu/css/light/loader.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}layouts/vertical-dark-menu/css/dark/loader.css" rel="stylesheet" type="text/css">
    <script src="{{ asset('') }}layouts/vertical-dark-menu/loader.js"></script>
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="{{ asset('') }}src/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}layouts/vertical-dark-menu/css/light/plugins.css" rel="stylesheet" type="text/css">
    <link href="{{ asset('') }}layouts/vertical-dark-menu/css/dark/plugins.css" rel="stylesheet" type="text/css">
    <style>
        .auth-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: 100%; max-width: 440px; }
    </style>
</head>
<body class="form-membership">

    <div class="auth-container">
        <div class="auth-card">
            <div class="card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold">Admin Login</h4>
                        <p class="text-muted">{{ config('app.name') }} Administration</p>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            <p class="mb-0">{{ $error }}</p>
                        @endforeach
                    </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Sign In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('') }}src/plugins/src/global/vendors.min.js"></script>
    <script src="{{ asset('') }}src/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('') }}layouts/vertical-dark-menu/app.js"></script>
</body>
</html>
