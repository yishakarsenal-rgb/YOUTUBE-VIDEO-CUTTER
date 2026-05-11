FROM php:8.2-apache

# Fix Apache MPM error for Railway
RUN a2dismod mpm_event && a2enmod mpm_prefork

# Install system dependencies (FFmpeg and Python)
RUN apt-get update && apt-get install -y \
    ffmpeg \
    python3 \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install yt-dlp directly
RUN curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Install PHP MySQL extension
RUN docker-php-ext-install pdo_mysql

# Copy your code into the server
COPY . /var/www/html/

# Create working folders and set permissions
RUN mkdir -p /var/www/html/downloads /var/www/html/temp /var/www/html/uploads \
    && chmod -R 777 /var/www/html/downloads /var/www/html/temp /var/www/html/uploads

# Set the port
ENV PORT=80
EXPOSE 80
