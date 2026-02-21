# --- STAGE 1: FRONTEND BUILDER ---
FROM node:20-slim AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- STAGE 2: FINAL PRODUCTION IMAGE ---
FROM php:8.2-apache

# 1. Install ONLY runtime system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libonig-dev libxml2-dev zip unzip curl \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd
RUN a2enmod rewrite

# 3. Set working directory
WORKDIR /var/www/html

# 4. Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- THE FIX: Copy your files BEFORE composer install ---
# 5. Copy everything (including artisan)
COPY . .

# 6. Install PHP dependencies
# Now artisan is present, so the post-install scripts will work
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 7. THE KEY OPTIMIZATION: 
# Copy the compiled assets from the builder stage
COPY --from=frontend-builder /app/public/build ./public/build

# 8. Set Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Configure Apache
ENV APACHE_DOCUMENT_ROOT="/var/www/html/public"
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 10. Enable SSH for Azure App Service
COPY sshd_config /etc/ssh/
COPY entrypoint.sh /usr/local/bin/
RUN apt-get update \
    && apt-get install -y --no-install-recommends openssh-server \
    && echo "root:Docker!" | chpasswd \
    && chmod u+x /usr/local/bin/entrypoint.sh \
    && rm -rf /var/lib/apt/lists/*

EXPOSE 80 2222

ENTRYPOINT [ "entrypoint.sh" ]