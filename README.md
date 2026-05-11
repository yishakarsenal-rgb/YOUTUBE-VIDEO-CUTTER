# YouTube Video Clipper - Installation Guide

A premium YouTube clipping application built with PHP, MySQL, yt-dlp, and FFmpeg.

## 🚀 Prerequisites

Before you begin, ensure you have the following installed:
1. **XAMPP / Laragon** (PHP 7.4+ & MySQL)
2. **FFmpeg** (Must be in your system's PATH)
3. **yt-dlp** (Must be in your system's PATH)

---

## 🛠️ Setup Instructions

### 1. Database Setup
1. Open **phpMyAdmin**.
2. Create a new database named `youtube_clipper`.
3. Import the `database/schema.sql` file.
4. (Optional) A default admin account is created:
   - **Username:** `admin`
   - **Password:** `admin123`

### 2. FFmpeg Installation
1. Download FFmpeg from [ffmpeg.org](https://ffmpeg.org/download.html).
2. Extract the files and add the `bin` folder to your Windows **Environment Variables (PATH)**.
3. Verify by running `ffmpeg -version` in your terminal.

### 3. yt-dlp Installation
1. Download the latest `yt-dlp.exe` from [yt-dlp GitHub](https://github.com/yt-dlp/yt-dlp/releases).
2. Place it in a folder (e.g., `C:\tools`) and add that folder to your **PATH**.
3. Verify by running `yt-dlp --version` in your terminal.

### 4. Folder Permissions
Ensure the following folders have **write permissions** (already set if using XAMPP on Windows):
- `/temp`
- `/downloads`
- `/uploads`

### 5. Running the App
1. Move the project folder to `C:\xampp\htdocs\`.
2. Start Apache and MySQL in XAMPP.
3. Visit `http://localhost/YOUTUBE%20VIDEO%20CUTTER/` in your browser.

---

## 🎨 Features
- **Glassmorphism UI**: Modern desktop-style design.
- **Fast Clipping**: Uses stream links to cut videos without downloading the whole file first.
- **Multiple Formats**: Export as MP4 or MP3.
- **Quality Selection**: Up to 1080p.
- **History System**: Keep track of your previous clips.
- **Admin Dashboard**: Monitor system statistics.

---

## ⚠️ Troubleshooting
- **Error fetching info**: Ensure `yt-dlp` is updated (`yt-dlp -U`).
- **FFmpeg failed**: Check if FFmpeg is correctly installed in the system PATH.
- **Slow processing**: Processing time depends on your internet speed and the selected quality.
