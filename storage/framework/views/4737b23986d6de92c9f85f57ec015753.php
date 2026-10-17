<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dawafy - Pharmacy Management Login</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: radial-gradient(circle at 10% 20%, rgba(37, 99, 235, 0.08) 0%, rgba(248, 250, 252, 1) 50%, rgba(16, 185, 129, 0.06) 100%);
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 1.5rem;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.06), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            width: 100%;
            max-width: 420px;
            padding: 2.5rem 2.25rem;
            animation: loginPop 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #10b981, #8b5cf6);
        }

        .brand-section {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-section img {
            max-height: 54px;
            margin-bottom: 0.85rem;
            transition: transform 0.3s ease;
        }

        .brand-section img:hover {
            transform: scale(1.05);
        }

        .brand-section h2 {
            font-weight: 800;
            color: #0f172a;
            font-size: 1.55rem;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }

        .brand-section p {
            color: #64748b;
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .form-floating > .form-control {
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            font-size: 0.9375rem;
            color: #0f172a;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-floating > .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .form-floating > label {
            color: #64748b;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: 1px solid #1d4ed8;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: #ffffff;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            margin-top: 0.75rem;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1.5px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
            color: #ffffff;
        }

        .btn-primary:active {
            transform: translateY(0) scale(0.98);
        }

        .alert {
            border-radius: 10px;
            font-size: 0.85rem;
            padding: 0.75rem 1rem;
        }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: #94a3b8;
            font-size: 0.78rem;
            border-top: 1px solid #f1f5f9;
            padding-top: 1.25rem;
        }

        @keyframes loginPop {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.96);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .footer-text a {
            color: #343a40;
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-section">
            <img src="<?php echo e(asset('assets/images/logo.png')); ?>" alt="Dawafy Logo" onerror="this.style.display='none'">
            <h2>Dawafy</h2>       
        </div>

        <?php if($errors->any()): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo e($errors->first()); ?>

            </div>
        <?php elseif(session('error')): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle-fill me-1"></i> <?php echo e(session('error')); ?>

            </div>
        <?php elseif(session('info')): ?>
            <div class="alert alert-info" role="alert">
                <i class="bi bi-info-circle-fill me-1"></i> <?php echo e(session('info')); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login.post')); ?>">
            <?php echo csrf_field(); ?>
            <div class="form-floating mb-3">
                <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" required value="<?php echo e(old('username')); ?>">
                <label for="floatingInput">Username</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password" required>
                <label for="floatingPassword">Password</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign In to Terminal</button>
        </form>

        <div class="footer-text">
            <div>&copy; <?php echo e(date('Y')); ?> Dawafy. All Rights Reserved.</div>
            <div class="mt-1">Powered by <a href="https://haulaenterprises.co.tz" target="_blank">Haula Enterprises</a></div>
        </div>
    </div>

</body>
</html>
<?php /**PATH C:\wamp64\www\pms\resources\views/auth/login.blade.php ENDPATH**/ ?>