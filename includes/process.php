<?php
require_once 'auth.php';
require_once 'db.php';

// Helper to find tool paths in different environments
function get_tool_path($tool) {
    $path = shell_exec("which $tool 2>/dev/null");
    if ($path) return trim($path);
    
    // Fallback common paths
    $fallbacks = [__DIR__ . "/../yt-dlp", "/usr/bin/$tool", "/usr/local/bin/$tool", "/app/bin/$tool"];
    foreach ($fallbacks as $f) {
        if (file_exists($f)) return $f;
    }
    
    // Special case for yt-dlp via python
    if ($tool === 'yt-dlp' && shell_exec("python3 -m yt_dlp --version 2>/dev/null")) {
        return "python3 -m yt_dlp";
    }
    
    return $tool; // Last resort
}

$ytdlp = get_tool_path('yt-dlp');
$ffmpeg = get_tool_path('ffmpeg');

// Increase execution time for video processing
set_time_limit(900); // 15 minutes
ini_set('max_execution_time', 900);

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'get_info') {
    $url = $_GET['url'] ?? '';
    if (empty($url)) {
        echo json_encode(['success' => false, 'message' => 'URL is required']);
        exit();
    }

    // Use yt-dlp to get video info
    $command = "$ytdlp --dump-json " . escapeshellarg($url);
    $output = shell_exec($command);
    
    if (!$output) {
        echo json_encode(['success' => false, 'message' => 'Could not fetch video info. Ensure yt-dlp is in PATH.']);
        exit();
    }

    $info = json_decode($output, true);
    if (!$info) {
        echo json_encode(['success' => false, 'message' => 'Failed to parse video info.']);
        exit();
    }

    $response = [
        'success' => true,
        'info' => [
            'title' => $info['title'],
            'thumbnail' => $info['thumbnail'],
            'duration' => $info['duration'],
            'duration_string' => gmdate("H:i:s", $info['duration']),
            'uploader' => $info['uploader']
        ]
    ];
    echo json_encode($response);
    exit();
}

if ($action === 'clip') {
    $url = $_POST['url'] ?? '';
    $start_time = $_POST['start_time'] ?? '00:00:00';
    $end_time = $_POST['end_time'] ?? '00:00:10';
    $format = $_POST['format'] ?? 'mp4';
    $quality = $_POST['quality'] ?? '720';

    if (empty($url)) {
        echo json_encode(['success' => false, 'message' => 'URL is required']);
        exit();
    }

    $downloads_dir = '../downloads/';
    if (!is_writable($downloads_dir)) {
        echo json_encode(['success' => false, 'message' => 'Download folder not writable.']);
        exit();
    }

    $file_id = uniqid('clip_');
    $temp_file = $downloads_dir . $file_id . '.tmp';
    $output_file = $downloads_dir . $file_id . '.' . ($format == 'mp3' ? 'mp3' : 'mp4');

    // 1. Get Title
    $video_title = trim(shell_exec("$ytdlp --get-title " . escapeshellarg($url)));

    // 2. Use yt-dlp's built-in section downloader (Requires FFmpeg in path)
    // This handles merging and URL signing automatically
    if ($format === 'mp3') {
        $cmd = "$ytdlp --extract-audio --audio-format mp3 --concurrent-fragments 5 --download-sections \"*$start_time-$end_time\" " . escapeshellarg($url) . " -o " . escapeshellarg($output_file) . " 2>&1";
    } else {
        $cmd = "$ytdlp -f \"bestvideo[height<=$quality][ext=mp4]+bestaudio[ext=m4a]/best[height<=$quality][ext=mp4]/best\" " .
               "--download-sections \"*$start_time-$end_time\" " .
               "--force-keyframes-at-cuts " .
               "--concurrent-fragments 5 " .
               "--ffmpeg-location " . escapeshellarg($ffmpeg) . " " .
               "-o " . escapeshellarg($temp_file) . " " .
               escapeshellarg($url) . " 2>&1";
    }

    $process_output = shell_exec($cmd);
    
    // Rename temp file if necessary
    if (file_exists($temp_file)) {
        rename($temp_file, $output_file);
    }

    // Because yt-dlp might append extension or slightly change name, we check for the file
    $final_file = $output_file;
    if (!file_exists($final_file)) {
        // Sometimes yt-dlp adds .mp4 even if we specify it
        if (file_exists($output_file . '.mp4')) $final_file = $output_file . '.mp4';
        if (file_exists($output_file . '.webm')) $final_file = $output_file . '.webm';
    }

    if (file_exists($final_file)) {
        $start_sec = strtotime("1970-01-01 $start_time UTC");
        $end_sec = strtotime("1970-01-01 $end_time UTC");
        
        $stmt = $pdo->prepare("INSERT INTO clip_history (user_id, video_url, video_title, start_time, end_time, duration, file_name, format, quality) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'], $url, $video_title, $start_time, $end_time, 
            gmdate("H:i:s", $end_sec - $start_sec), basename($final_file), $format, $quality
        ]);

        echo json_encode([
            'success' => true,
            'file_url' => 'downloads/' . basename($final_file),
            'file_name' => $video_title . '_clip.' . $format
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Clipping failed. See log below.',
            'debug' => $process_output,
            'cmd' => $cmd
        ]);
    }
    exit();
}
?>
