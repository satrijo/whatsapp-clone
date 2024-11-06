# Gunakan base image php:8.2-fpm
FROM php:8.2-fpm

# Install dependencies sistem
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    supervisor \
    libpq-dev \
    nodejs \
    npm

# Bersihkan cache apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install ekstensi PHP yang diperlukan
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

# Install ekstensi Redis untuk PHP
RUN pecl install redis && docker-php-ext-enable redis

# Copy Composer dari image composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set direktori kerja
WORKDIR /var/www

# Buat direktori yang diperlukan dan atur permission
RUN mkdir -p \
    /var/log/supervisor \
    /var/run/supervisor \
    /var/run/php-fpm \
    && chmod -R 777 \
    /var/log/supervisor \
    /var/run/supervisor \
    /var/run/php-fpm

# Copy semua file aplikasi ke dalam container
COPY . .

# Install Composer dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Install NPM dependencies
RUN npm install && npm run build

# Set permission
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Copy konfigurasi Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port untuk PHP-FPM
EXPOSE 9000

# Jalankan Supervisor sebagai perintah default
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
