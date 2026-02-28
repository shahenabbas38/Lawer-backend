# صورة تشغيل Laravel + Calibre (تحويل EPUB → PDF) على Railway
FROM php:8.2-cli-bookworm

# حزم النظام + إضافات PHP للارافيل
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev \
    zip \
    unzip \
    git \
    wget \
    xz-utils \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql zip bcmath pcntl gd mbstring xml \
    && rm -rf /var/lib/apt/lists/*

# تثبيت Calibre (ebook-convert) لتحويل EPUB → PDF
# إصدار ثابت متوافق مع Debian Bookworm
ARG CALIBRE_VERSION=6.29.0
RUN wget -q -O /tmp/calibre.txz "https://download.calibre-ebook.com/${CALIBRE_VERSION}/calibre-${CALIBRE_VERSION}-x86_64.txz" \
    && tar xf /tmp/calibre.txz -C /opt \
    && CALDIR=$(ls -d /opt/calibre-* 2>/dev/null | head -1) \
    && ln -sf "$CALDIR" /opt/calibre-bin \
    && ( [ -x /opt/calibre-bin/calibre_postinstall ] && /opt/calibre-bin/calibre_postinstall 2>/dev/null || true ) \
    && rm /tmp/calibre.txz

ENV PATH="/opt/calibre-bin:${PATH}"

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# تثبيت الاعتماديات (بدون dev للاستضافة)
RUN composer install --no-dev --no-interaction --optimize-autoloader

# صلاحيات التخزين
RUN mkdir -p storage/app/public storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chmod -R 775 storage bootstrap/cache

# المنفذ يُحدد من متغير PORT على Railway
EXPOSE 8000

# يمكن لـ Railway استبدال هذا الأمر بـ Start Command من لوحة التحكم
CMD ["sh", "-c", "php artisan migrate --force 2>/dev/null || true && php artisan storage:link 2>/dev/null || true && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
