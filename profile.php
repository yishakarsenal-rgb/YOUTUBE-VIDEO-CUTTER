<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$clip_count = $pdo->prepare("SELECT COUNT(*) FROM clip_history WHERE user_id = ?");
$clip_count->execute([$_SESSION['user_id']]);
$total_clips = $clip_count->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - YouTube Video Clipper</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-header {
            padding: 3rem;
            text-align: center;
            margin-bottom: 2rem;
        }
        .avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary-gradient);
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header style="margin-bottom: 3rem;">
            <h1>My Profile</h1>
            <p style="color: var(--text-muted);">Manage your account and preferences</p>
        </header>

        <div class="profile-container">
            <div class="profile-header glass-card animate-fade-in">
                <div class="avatar-large">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <div style="display: flex; justify-content: center; gap: 3rem; border-top: 1px solid var(--glass-border); padding-top: 2rem;">
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 700;"><?php echo $total_clips; ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Clips Created</div>
                    </div>
                    <div>
                        <div style="font-size: 1.5rem; font-weight: 700;"><?php echo date('M Y', strtotime($user['created_at'])); ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Member Since</div>
                    </div>
                </div>
            </div>

            <div class="glass-card" style="padding: 2rem;">
                <h3>Account Settings</h3>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem;">Update your information (Implementation pending)</p>
                
                <form>
                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    <div class="input-group">
                        <label>Email</label>
                        <input type="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    <button type="button" class="btn btn-primary" style="opacity: 0.5; cursor: not-allowed;">Save Changes</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
