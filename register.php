<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerUser($username, $email, $password);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - YouTube Video Clipper</title>
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
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid var(--accent); color: #d1fae5; }
    </style>
</head>
<body>
    <div class="auth-card glass-card animate-fade-in">
        <h2 style="margin-bottom: 0.5rem;">Join Us</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Create an account to start clipping</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?>. <a href="login.php" style="color: white;">Login here</a></div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" class="form-input" placeholder="johndoe" required>
            </div>
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="john@example.com" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-input" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Create Account</button>
        </form>

        <p style="text-align: center; margin-top: 2rem; color: var(--text-muted);">
            Already have an account? <a href="login.php" style="color: #6366f1; text-decoration: none; font-weight: 600;">Sign In</a>
        </p>
    </div>
</body>
</html>
