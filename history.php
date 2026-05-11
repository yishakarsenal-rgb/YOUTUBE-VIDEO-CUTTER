<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT file_name FROM clip_history WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
    $clip = $stmt->fetch();
    
    if ($clip) {
        $file_path = 'downloads/' . $clip['file_name'];
        if (file_exists($file_path)) unlink($file_path);
        
        $stmt = $pdo->prepare("DELETE FROM clip_history WHERE id = ?");
        $stmt->execute([$id]);
    }
    header("Location: history.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM clip_history WHERE user_id = ? ORDER BY download_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$history = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - YouTube Video Clipper</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .history-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .history-card {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .clip-meta {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 5px;
            font-size: 0.7rem;
            background: var(--accent);
            color: white;
            width: fit-content;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header style="margin-bottom: 3rem;">
            <h1>Clip History</h1>
            <p style="color: var(--text-muted);">View and manage your generated clips</p>
        </header>

        <div class="history-grid">
            <?php if (empty($history)): ?>
                <div class="glass-card" style="padding: 3rem; text-align: center; grid-column: 1 / -1;">
                    <p style="color: var(--text-muted);">No clips found yet. Go to dashboard to create one!</p>
                </div>
            <?php else: ?>
                <?php foreach ($history as $clip): ?>
                <div class="history-card glass-card animate-fade-in">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <h3 style="font-size: 1.1rem; line-height: 1.4;"><?php echo htmlspecialchars($clip['video_title']); ?></h3>
                        <span class="status-badge"><?php echo strtoupper($clip['format']); ?></span>
                    </div>
                    
                    <div class="clip-meta">
                        <div>📅 <?php echo date('M d, Y', strtotime($clip['download_date'])); ?></div>
                        <div>⏱️ Range: <?php echo $clip['start_time']; ?> - <?php echo $clip['end_time']; ?></div>
                        <div>📐 Quality: <?php echo $clip['quality']; ?>p</div>
                    </div>

                    <div style="display: flex; gap: 1rem; margin-top: auto;">
                        <a href="downloads/<?php echo $clip['file_name']; ?>" download class="btn btn-primary" style="flex: 1; justify-content: center; padding: 0.6rem;">
                            Download
                        </a>
                        <a href="history.php?delete=<?php echo $clip['id']; ?>" class="btn" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2);" onclick="return confirm('Are you sure?')">
                            🗑️
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
