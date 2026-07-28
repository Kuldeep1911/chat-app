FROM php:8.4-apache

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip


# Enable Apache rewrite
RUN a2enmod rewrite


# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Install Node.js 24
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs


# Copy project files
COPY . .


# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader


# Install frontend dependencies
RUN npm install


# Build Vite assets
RUN npm run build


# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

RUN chmod -R 775 storage bootstrap/cache


# Apache document root -> Laravel public folder
RUN sed -i 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/000-default.conf


# Environment
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public


EXPOSE 80


CMD ["apache2-foreground"]