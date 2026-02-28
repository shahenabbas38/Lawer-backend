FROM php:8.2-cli-bookworm

# تثبيت الحزم والمكتبات اللازمة لـ Calibre و Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev zip unzip git wget xz-utils \
    libfreetype6-dev libjpeg62-turbo-dev libpng-dev libonig-dev libxml2-dev libpq-dev \
    libgl1 libnss3 libcomposite0 libfontconfig1 libxext6 libxrender1 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql zip bcmath pcntl gd mbstring xml \
    && rm -rf /var/lib/apt/lists/*

# تثبيت Calibre
ARG CALIBRE_VERSION=6.29.0
RUN wget -q -O /tmp/calibre.txz "https://download.calibre-ebook.com/${CALIBRE_VERSION}/calibre-${CALIBRE_VERSION}-x86_64.txz" \
    && tar xf /tmp/calibre.txz -C /opt \
    && rm /tmp/calibre.txz

ENV PATH="/opt/calibre:${PATH}"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-dev --no-interaction --optimize-autoloader
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

# استخدام صيغة exec لضمان بقاء الحاوية Online
CMD ["sh", "-c", "php artisan migrate --force && php artisan storage:link && (php artisan queue:work --tries=3 &) && php artisan serve --host=0.0.0.0 --port=8080"]