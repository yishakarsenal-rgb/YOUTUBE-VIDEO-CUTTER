<?php
require_once 'includes/auth.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - YouTube Video Clipper</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .video-load-section {
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .preview-section {
            display: none; /* Shown after load */
            gap: 2rem;
            margin-top: 2rem;
        }
        .video-preview {
            flex: 2;
            overflow: hidden;
        }
        .clip-controls {
            flex: 1;
            padding: 2rem;
        }
        #video-thumbnail {
            width: 100%;
            border-radius: 15px;
            margin-bottom: 1rem;
        }
        .progress-container {
            display: none;
            margin-top: 2rem;
        }
        .progress-bar {
            height: 10px;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: var(--primary-gradient);
            width: 0%;
            transition: width 0.3s ease;
        }
        .timeline-slider {
            width: 100%;
            height: 6px;
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
            position: relative;
            margin: 2rem 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <h1>Dashboard</h1>
                <p style="color: var(--text-muted);">Paste a YouTube URL to start clipping</p>
            </div>
            <div class="theme-toggle glass-card" style="padding: 0.5rem 1rem; cursor: pointer;">
                🌙 Dark Mode
            </div>
        </header>

        <!-- System Diagnostic -->
        <div class="glass-card animate-fade-in" style="padding: 1rem; margin-bottom: 2rem; display: flex; gap: 2rem; align-items: center; border-left: 4px solid #6366f1;">
            <div style="font-weight: 600; font-size: 0.9rem;">System Check:</div>
            <div style="font-size: 0.8rem;">FFmpeg: <?php echo shell_exec('which ffmpeg') ? '<span style="color:var(--accent)">✅ Ready</span>' : '<span style="color:var(--danger)">❌ Not Found</span>'; ?></div>
            <div style="font-size: 0.8rem;">yt-dlp: <?php echo shell_exec('which yt-dlp') ? '<span style="color:var(--accent)">✅ Ready</span>' : '<span style="color:var(--danger)">❌ Not Found</span>'; ?></div>
            <div style="font-size: 0.8rem;">Write Perms: <?php echo is_writable('downloads') ? '<span style="color:var(--accent)">✅ OK</span>' : '<span style="color:var(--danger)">❌ Restricted</span>'; ?></div>
        </div>

        <div class="dashboard-container">
            <!-- URL Input Section -->
            <section class="video-load-section glass-card animate-fade-in">
                <div style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div class="input-group" style="flex: 1; margin-bottom: 0;">
                        <label>YouTube Video URL</label>
                        <input type="text" id="video-url" class="form-input" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <button id="load-btn" class="btn btn-primary" style="height: 52px; padding: 0 2rem;">
                        <span id="load-text">Load Video</span>
                        <div id="load-spinner" class="loader" style="width: 20px; height: 20px; display: none;"></div>
                    </button>
                </div>
            </section>

            <!-- Video Info & Preview Section -->
            <section id="preview-section" class="preview-section animate-fade-in">
                <div class="video-preview glass-card">
                    <img id="video-thumbnail" src="" alt="Thumbnail">
                    <h3 id="video-title" style="margin-bottom: 0.5rem;">Video Title</h3>
                    <p id="video-meta" style="color: var(--text-muted); font-size: 0.9rem;">Duration: 00:00:00 • Channel Name</p>
                    
                    <div class="timeline-slider">
                        <!-- Custom timeline slider implementation here -->
                    </div>
                </div>

                <div class="clip-controls glass-card">
                    <h3>Clip Settings</h3>
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 1.5rem;">Select your range and format</p>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="input-group">
                            <label>Start Time</label>
                            <input type="text" id="start-time" class="form-input" placeholder="00:00:00" value="00:00:00">
                        </div>
                        <div class="input-group">
                            <label>End Time</label>
                            <input type="text" id="end-time" class="form-input" placeholder="00:00:10" value="00:00:10">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Format</label>
                        <select id="format" class="form-input">
                            <option value="mp4">MP4 Video</option>
                            <option value="mp3">MP3 Audio</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label>Quality</label>
                        <select id="quality" class="form-input">
                            <option value="1080">1080p Full HD</option>
                            <option value="720">720p HD</option>
                            <option value="360">360p Standard</option>
                        </select>
                    </div>

                    <button id="clip-btn" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                        ✂️ Clip & Download
                    </button>

                    <div id="progress-section" class="progress-container">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem;">
                            <span id="progress-status">Processing...</span>
                            <span id="progress-percent">0%</span>
                        </div>
                        <div class="progress-bar">
                            <div id="progress-fill" class="progress-fill"></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
