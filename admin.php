<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Stats
$total_clips = $pdo->query("SELECT COUNT(*) FROM clip_history")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$recent_clips = $pdo->query("SELECT h.*, u.username FROM clip_history h JOIN users u ON h.user_id = u.id ORDER BY download_date DESC LIMIT 10")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - YouTube Video Clipper</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            padding: 2rem;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            display: block;
            margin-bottom: 0.5rem;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .admin-table th, .admin-table td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
        }
        .admin-table th { color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header style="margin-bottom: 3rem;">
            <h1>Admin Panel</h1>
            <p style="color: var(--text-muted);">System overview and management</p>
        </header>

        <div class="stats-container">
            <div class="stat-card glass-card">
                <span class="stat-number gradient-text"><?php echo $total_clips; ?></span>
                <span style="color: var(--text-muted);">Total Clips</span>
            </div>
            <div class="stat-card glass-card">
                <span class="stat-number gradient-text"><?php echo $total_users; ?></span>
                <span style="color: var(--text-muted);">Total Users</span>
            </div>
            <div class="stat-card glass-card">
                <span class="stat-number gradient-text">Live</span>
                <span style="color: var(--text-muted);">System Status</span>
            </div>
        </div>

        <section class="glass-card" style="padding: 2rem;">
            <h3>Recent Downloads</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Video Title</th>
                        <th>Format</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_clips as $clip): ?>
                    <tr>
                        <td style="font-weight: 600;"><?php echo htmlspecialchars($clip['username']); ?></td>
                        <td><?php echo htmlspecialchars($clip['video_title']); ?></td>
                        <td><span class="status-badge"><?php echo $clip['format']; ?></span></td>
                        <td style="color: var(--text-muted);"><?php echo date('M d, H:i', strtotime($clip['download_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>
