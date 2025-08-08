# ===============================
# Stage 1: Build frontend assets
# ===============================
FROM node:20 AS nodebuild

WORKDIR /app

# Copy only package files first (better layer caching)
COPY package*.json ./

# Install frontend dependencies
RUN npm install

# Copy rest of the app
COPY . .

# Build Vue frontend
RUN npm run build


# ===============================
# Stage 2: Laravel backend with Nginx & PHP
# ===============================
FROM webdevops/php-nginx:8.2

# Set working directory
WORKDIR /app

# Copy built app from node stage
COPY --from=nodebuild /app /app

# Install Composer (from official composer image)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Laravel PHP dependencies and cache configs
RUN composer install --no-dev --optimize-autoloader && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Optional: fix file permissions
RUN chown -R application:application /app

# Expose default web port
EXPOSE 80