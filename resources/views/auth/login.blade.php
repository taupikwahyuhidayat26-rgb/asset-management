<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - VeloAssets</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { color-scheme: light; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: radial-gradient(ellipse at 18% 15%, rgba(99, 102, 241, .25), transparent 35%),
                        radial-gradient(ellipse at 90% 85%, rgba(14, 165, 233, .16), transparent 34%),
                        #101827;
            color: #172033;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .login-card {
            width: min(100%, 440px);
            padding: 42px;
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(255,255,255,.55);
            border-radius: 24px;
            box-shadow: 0 28px 80px rgba(2, 8, 23, .35);
        }
        .brand-mark {
            width: 58px;
            height: 58px;
            color: white;
            background: linear-gradient(135deg, #6366f1, #4338ca);
            box-shadow: 0 10px 24px rgba(79, 70, 229, .26);
        }
        .form-control {
            min-height: 48px;
            padding: 12px 15px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            font-size: .9rem;
        }
        .form-control:focus {
            background: #fff;
            border-color: #818cf8;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .12);
        }
        .btn-premium {
            padding: 13px;
            color: #fff;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: 0;
            border-radius: 11px;
            font-weight: 700;
            box-shadow: 0 8px 18px rgba(79, 70, 229, .2);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .btn-premium:hover {
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 12px 22px rgba(79, 70, 229, .27);
        }
        @media (max-width: 480px) { .login-card { padding: 32px 24px; } }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <div class="mb-4">
            <div class="brand-mark d-inline-flex align-items-center justify-content-center rounded-4 mb-3">
                <i class="fas fa-box-open fs-4"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Selamat datang</h4>
            <p class="text-muted small mb-0">Masuk untuk melanjutkan ke VeloAssets</p>
        </div>

        @if($errors->has('loginError'))
            <div class="alert alert-danger border-0 rounded-3 small py-2 text-start" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> {{ $errors->first('loginError') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">            
            @csrf
            <div class="mb-3 text-start">
                <label for="username" class="form-label small fw-semibold text-secondary">Username</label>
                <input id="username" type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus autocomplete="username" placeholder="Masukkan username">
            </div>
            
            <div class="mb-4 text-start">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="Masukkan password">
            </div>

            <button type="submit" class="btn btn-premium w-100 mb-2">
                Masuk <i class="fas fa-arrow-right small ms-1"></i>
            </button>
        </form>
    </div>

</body>
</html>