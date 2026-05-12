document.addEventListener('DOMContentLoaded', () => {
    const loadBtn = document.getElementById('load-btn');
    const loadText = document.getElementById('load-text');
    const loadSpinner = document.getElementById('load-spinner');
    const urlInput = document.getElementById('video-url');
    const previewSection = document.getElementById('preview-section');
    
    const clipBtn = document.getElementById('clip-btn');
    const progressSection = document.getElementById('progress-section');
    const progressFill = document.getElementById('progress-fill');
    const progressStatus = document.getElementById('progress-status');
    const progressPercent = document.getElementById('progress-percent');

    // Load Video Info
    loadBtn.addEventListener('click', async () => {
        const url = urlInput.value.trim();
        if (!url) {
            alert('Please enter a YouTube URL');
            return;
        }

        // UI Loading State
        loadText.style.display = 'none';
        loadSpinner.style.display = 'inline-block';
        loadBtn.disabled = true;

        try {
            const response = await fetch('includes/process.php?action=get_info&url=' + encodeURIComponent(url));
            const data = await response.json();

            if (data.title) {
                document.getElementById('video-thumbnail').src = data.thumbnail;
                document.getElementById('video-title').textContent = data.title;
                document.getElementById('video-meta').textContent = `Duration: ${data.duration_str}`;
                
                // Show preview section
                previewSection.style.display = 'flex';
                previewSection.scrollIntoView({ behavior: 'smooth' });
                
                // Set default end time based on duration (or max 10s)
                document.getElementById('end-time').value = data.duration > 10 ? '00:00:10' : data.duration_str;
            } else {
                alert('Error: ' + (data.error || 'Failed to fetch info'));
            }
        } catch (error) {
            alert('An error occurred while fetching video info.');
            console.error(error);
        } finally {
            loadText.style.display = 'inline-block';
            loadSpinner.style.display = 'none';
            loadBtn.disabled = false;
        }
    });

    // Start Clipping
    clipBtn.addEventListener('click', async () => {
        const url = urlInput.value.trim();
        const startTime = document.getElementById('start-time').value;
        const endTime = document.getElementById('end-time').value;
        const format = document.getElementById('format').value;
        const quality = document.getElementById('quality').value;

        // UI Progress State
        progressSection.style.display = 'block';
        clipBtn.disabled = true;
        updateProgress(10, 'Downloading video...');

        const formData = new FormData();
        formData.append('action', 'clip_video');
        formData.append('url', url);
        formData.append('start_time', startTime);
        formData.append('end_time', endTime);
        formData.append('format', format);
        formData.append('quality', quality);

        try {
            updateProgress(30, 'Processing with FFmpeg...');
            const response = await fetch('includes/process.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.file_url) {
                updateProgress(100, 'Success!');
                progressStatus.innerHTML = `Done! <button onclick="copyToClipboard('${window.location.origin + '/' + data.file_url}')" class="btn" style="padding: 2px 8px; font-size: 0.7rem; background: rgba(255,255,255,0.1); margin-left: 10px;">📋 Copy Link</button>`;
                
                // Automatically trigger download
                const a = document.createElement('a');
                a.href = data.file_url;
                a.download = data.file_name;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                
                alert('Clip generated successfully!');
            } else {
                updateProgress(0, 'Failed');
                alert(data.error || 'Processing failed');
            }
        } catch (error) {
            alert('An error occurred during processing.');
            console.error(error);
            updateProgress(0, 'Error');
        } finally {
            clipBtn.disabled = false;
        }
    });

    function updateProgress(percent, status) {
        progressFill.style.width = percent + '%';
        progressPercent.textContent = percent + '%';
        progressStatus.textContent = status;
    }

    // Theme Toggle
    const themeToggle = document.querySelector('.theme-toggle');
    themeToggle.addEventListener('click', () => {
        const currentTheme = document.body.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        document.body.setAttribute('data-theme', newTheme);
        themeToggle.textContent = newTheme === 'light' ? '☀️ Light Mode' : '🌙 Dark Mode';
    });
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Link copied to clipboard!');
    }).catch(err => {
        console.error('Could not copy text: ', err);
    });
}
