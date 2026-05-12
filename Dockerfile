FROM php:8.2-cli-alpine

# Install system dependencies (Alpine version)
RUN apk add --no-cache \
    ffmpeg \
    python3 \
    curl \
    mariadb-dev \
    build-base \
    nodejs

# Install PHP MySQL extension
RUN docker-php-ext-install pdo_mysql

# Install yt-dlp
RUN curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o /usr/local/bin/yt-dlp \
    && chmod a+rx /usr/local/bin/yt-dlp

# Set up the app
COPY . /app
WORKDIR /app

# Create working folders and set permissions
RUN mkdir -p downloads temp uploads \
    && chmod -R 777 downloads temp uploads

# Use the PORT provided by Railway and list modules for debugging
CMD ["sh", "-c", "php -m && php -S 0.0.0.0:${PORT:-80} -t ."]
