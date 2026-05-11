<div class="sidebar glass-card">
    <div class="logo" style="margin-bottom: 3rem;">
        <h2 class="gradient-text">YT Clipper</h2>
    </div>
    
    <nav style="flex: 1;">
        <ul style="list-style: none;">
            <li style="margin-bottom: 1rem;">
                <a href="index.php" class="btn" style="width: 100%; justify-content: flex-start; background: <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'rgba(255,255,255,0.1)' : 'transparent'; ?>;">
                    <span style="font-size: 1.2rem;">🏠</span> Dashboard
                </a>
            </li>
            <li style="margin-bottom: 1rem;">
                <a href="history.php" class="btn" style="width: 100%; justify-content: flex-start; background: <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'rgba(255,255,255,0.1)' : 'transparent'; ?>;">
                    <span style="font-size: 1.2rem;">📜</span> History
                </a>
            </li>
            <li style="margin-bottom: 1rem;">
                <a href="profile.php" class="btn" style="width: 100%; justify-content: flex-start; background: <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'rgba(255,255,255,0.1)' : 'transparent'; ?>;">
                    <span style="font-size: 1.2rem;">👤</span> Profile
                </a>
            </li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li style="margin-bottom: 1rem;">
                <a href="admin.php" class="btn" style="width: 100%; justify-content: flex-start; background: <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'rgba(255,255,255,0.1)' : 'transparent'; ?>;">
                    <span style="font-size: 1.2rem;">🛡️</span> Admin Panel
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="user-info glass-card" style="padding: 1rem; margin-top: auto; display: flex; align-items: center; gap: 1rem;">
        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--primary-gradient);"></div>
        <div style="flex: 1;">
            <div style="font-weight: 600; font-size: 0.9rem;"><?php echo $_SESSION['username']; ?></div>
            <div style="font-size: 0.7rem; color: var(--text-muted);">Standard User</div>
        </div>
        <a href="logout.php" style="text-decoration: none; font-size: 1.2rem;">🚪</a>
    </div>
</div>
