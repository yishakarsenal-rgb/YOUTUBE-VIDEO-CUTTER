<?php
require_once 'auth.php';
require_once 'db.php';

// Helper to find tool paths in different environments
function get_tool_path($tool) {
    $output = [];
    exec("which $tool 2>/dev/null", $output);
    $path = !empty($output) ? trim($output[0]) : null;
    if ($path) return $path;
    
    // Fallback common paths
    $fallbacks = [__DIR__ . "/../yt-dlp", "/usr/bin/$tool", "/usr/local/bin/$tool", "/app/bin/$tool"];
    foreach ($fallbacks as $f) {
        if (file_exists($f)) return $f;
    }
    
    return $tool; // Last resort
}

$ytdlp = get_tool_path('yt-dlp');
$ffmpeg = get_tool_path('ffmpeg');

// Increase execution time for video processing
set_time_limit(900); // 15 minutes
ini_set('max_execution_time', 900);

$shell_disabled = !function_exists('exec') || strpos(ini_get('disable_functions'), 'exec') !== false;

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
    $command = "$ytdlp --dump-json --no-warnings " . escapeshellarg($url);
    $output_lines = [];
    exec("$command 2>&1", $output_lines);
    $output = implode("\n", $output_lines);
    
    // Find the actual JSON object (it might be surrounded by warnings)
    $start = strpos($output, '{');
    $end = strrpos($output, '}');
    
    if ($start === false || $end === false) {
        die(json_encode(['error' => 'Could not find video data. Details: ' . substr($output, 0, 200)]));
    }

    $json = substr($output, $start, $end - $start + 1);
    $data = json_decode($json, true);
    
    if (!$data) {
        die(json_encode(['error' => 'Failed to parse video data. Response was not valid JSON.']));
    }

    echo json_encode([
        'title' => $data['title'] ?? 'Unknown Title',
        'thumbnail' => $data['thumbnail'] ?? '',
        'duration' => $data['duration'] ?? 0,
        'duration_str' => gmdate("H:i:s", $data['duration'] ?? 0)
    ]);
    exit;
}

if ($action === 'clip_video') {
    $url = $_POST['url'] ?? '';
    $start_time = $_POST['start_time'] ?? '00:00:00';
    $end_time = $_POST['end_time'] ?? '00:00:10';
    $quality = $_POST['quality'] ?? '720';
    $format = $_POST['format'] ?? 'mp4';

    if (empty($url)) {
        die(json_encode(['error' => 'Video URL is required']));
    }

    $downloads_dir = __DIR__ . '/../downloads/';
    if (!file_exists($downloads_dir)) {
        mkdir($downloads_dir, 0777, true);
    }

    $file_id = uniqid('clip_');
    $temp_file = $downloads_dir . $file_id . '.tmp';
    $output_file = $downloads_dir . $file_id . '.' . ($format == 'mp3' ? 'mp3' : 'mp4');

    // 1. Get Title
    $title_out = [];
    exec("$ytdlp --get-title " . escapeshellarg($url), $title_out);
    $video_title = !empty($title_out) ? trim($title_out[0]) : 'YouTube Clip';

    // 2. Use yt-dlp's built-in section downloader
    if ($format === 'mp3') {
        $cmd = "$ytdlp --extract-audio --audio-format mp3 --concurrent-fragments 5 --download-sections \"*$start_time-$end_time\" " . escapeshellarg($url) . " -o " . escapeshellarg($output_file);
    } else {
        $cmd = "$ytdlp -f \"bestvideo[height<=$quality][ext=mp4]+bestaudio[ext=m4a]/best[height<=$quality][ext=mp4]/best\" " .
               "--download-sections \"*$start_time-$end_time\" " .
               "--force-keyframes-at-cuts " .
               "--concurrent-fragments 5 " .
               "--ffmpeg-location " . escapeshellarg($ffmpeg) . " " .
               "-o " . escapeshellarg($temp_file) . " " .
               escapeshellarg($url);
    }

    $process_out = [];
    exec("$cmd 2>&1", $process_out);
    $process_output = implode("\n", $process_out);
    
    // Rename temp file if necessary
    if (file_exists($temp_file)) {
        rename($temp_file, $output_file);
    }

    if (file_exists($output_file)) {
        $start_sec = strtotime("1970-01-01 $start_time UTC");
        $end_sec = strtotime("1970-01-01 $end_time UTC");
        
        $stmt = $pdo->prepare("INSERT INTO clip_history (user_id, video_url, video_title, start_time, end_time, duration, file_name, format, quality) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'], $url, $video_title, $start_time, $end_time, 
            gmdate("H:i:s", $end_sec - $start_sec), basename($output_file), $format, $quality
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
