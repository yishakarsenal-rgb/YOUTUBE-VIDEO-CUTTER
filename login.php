<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $result = loginUser($username, $password);
    if ($result['success']) {
        header("Location: index.php");
        exit();
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - YouTube Video Clipper</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
        }
        .auth-card {
            width: 100%;
            max-width: 450px;
            padding: 3rem;
        }
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid var(--danger); color: #fecaca; }
    </style>
</head>
<body>
    <div class="auth-card glass-card animate-fade-in">
        <h2 style="margin-bottom: 0.5rem;">Welcome Back</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Login to access your clips</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" class="form-input" placeholder="johndoe" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Sign In</button>
        </form>

        <p style="text-align: center; margin-top: 2rem; color: var(--text-muted);">
            Don't have an account? <a href="register.php" style="color: #6366f1; text-decoration: none; font-weight: 600;">Sign Up</a>
        </p>
    </div>
</body>
</html>
