# Use official PHP 8.3 with Apache
FROM php:8.3-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_sqlite \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache for Symfony
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-scripts --no-autoloader

# Copy the rest of the application
COPY . .

# Dump optimized autoloader
RUN composer dump-autoload --optimize

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create necessary directories
RUN mkdir -p var/cache var/log var/data \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

# Create entrypoint script
RUN echo '#!/bin/bash\n\
set -e\n\
echo "🚀 Starting MyPOS Vehicle Marketplace..."\n\
echo "Checking dependencies..."\n\
if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then\n\
    echo "Dependencies missing, running composer install..."\n\
    composer install --optimize-autoloader\n\
fi\n\
echo "Creating .env file from environment variables..."\n\
cat > .env << EOF\n\
APP_ENV=${APP_ENV:-dev}\n\
APP_DEBUG=${APP_DEBUG:-1}\n\
APP_SECRET=${APP_SECRET:-your-secret-key-here}\n\
DATABASE_URL=${DATABASE_URL:-sqlite:///%kernel.project_dir%/var/data/data_dev.db}\n\
MAILER_DSN=${MAILER_DSN:-null://null}\n\
FROM_EMAIL=${FROM_EMAIL:-noreply@mypos-carmarket.com}\n\
EOF\n\
echo "Setting up SQLite database..."\n\
mkdir -p var/data\n\
echo "Running database migrations..."\n\
php bin/console doctrine:migrations:migrate --no-interaction\n\
echo "Seeding database with test data..."\n\
php bin/console app:seed-data\n\
echo "Clearing cache..."\n\
php bin/console cache:clear\n\
echo "✅ Starting Apache server..."\n\
exec apache2-foreground' > /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80
EXPOSE 80

# Use entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]