FROM php:8.2-cli

# Install system dependencies (Standard Debian version)
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    python3-pip \
    curl \
    nodejs \
    libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP MySQL extension
RUN docker-php-ext-install pdo_mysql

# Install yt-dlp via Pip
RUN pip3 install yt-dlp --break-system-packages

# Set up the app
COPY . /app
WORKDIR /app

# Create working folders and set permissions
RUN mkdir -p downloads temp uploads \
    && chmod -R 777 downloads temp uploads

# FORCE the extension to load in the start command
CMD ["php", "-d", "extension=pdo_mysql.so", "-S", "0.0.0.0:${PORT:-80}", "-t", "."]
