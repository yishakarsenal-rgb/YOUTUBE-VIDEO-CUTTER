FROM php:8.2-cli

# Install system dependencies (Standard Debian version)
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    curl \
    nodejs \
    libmariadb-dev-compat \
    libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

# Install yt-dlp via Pip (Most reliable method)
RUN pip3 install yt-dlp --break-system-packages

# Set up the app
COPY . /app
WORKDIR /app

# Create working folders and set permissions
RUN mkdir -p downloads temp uploads \
    && chmod -R 777 downloads temp uploads

# Use the PORT provided by Railway and list modules for debugging
CMD ["sh", "-c", "php -m && php -S 0.0.0.0:${PORT:-80} -t ."]
