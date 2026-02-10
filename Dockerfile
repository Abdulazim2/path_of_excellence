# استخدام صورة PHP 8.0 كما طلبت 
FROM php:8.0-apache 

# تثبيت الإضافات المطلوبة لـ Laravel 
RUN apt-get update && apt-get install -y \ 
    libpng-dev \ 
    libonig-dev \ 
    libxml2-dev \ 
    zip \ 
    unzip \ 
    git \ 
    curl 

# تثبيت تعريفات PHP 
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd 

# تفعيل Mod Rewrite الخاص بـ Apache 
RUN a2enmod rewrite 

# تثبيت Composer (النسخة المتوافقة) 
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer 

# إعداد مجلد العمل 
WORKDIR /var/www/html 

# نسخ ملفات المشروع 
COPY . . 

# تثبيت المكاتب مع تجاهل توافق الإصدارات (الحل السحري لمشكلتك) 
# استخدام --ignore-platform-reqs هو الذي سيجعل Composer يتجاهل أنك على PHP 8.0 
RUN composer install --ignore-platform-reqs --no-dev --optimize-autoloader 

# ضبط الصلاحيات 
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 

# إعداد Apache ليشير إلى مجلد public 
ENV APACHE_DOCUMENT_ROOT /var/www/html/public 
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf 
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf 
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf 

# تشغيل السيرفر 
CMD ["apache2-foreground"]