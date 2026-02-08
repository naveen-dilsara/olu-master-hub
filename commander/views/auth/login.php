<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | OLU Master Hub</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .login-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, #1f2937 0%, #0a0f1c 100%);
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            background-color: rgba(17, 24, 39, 0.9);
            border: var(--border-glass);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-glow);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--color-text-muted);
            font-size: 0.875rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--color-bg-hover);
            border-radius: var(--radius-md);
            color: var(--color-text-main);
            font-size: 1rem;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .btn-primary {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
            border: none;
            border-radius: var(--radius-md);
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .error-msg {
            color: var(--color-danger);
            margin-bottom: 1rem;
            font-size: 0.875rem;
            text-align: center;
        }
        .brand-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
            margin-bottom: 2rem;
        }
        .brand-title span { color: white; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="brand-title">OLU <span>Master Hub</span></div>
            
            <?php if(isset($error)): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <div class="form-group">
                    <label class="form-label">Email / Username</label>
                    <input type="text" name="username" class="form-input" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-input" required>
                </div>
                <button type="submit" class="btn-primary">Authenticate</button>
            </form>
        </div>
    </div>
</body>
</html>
