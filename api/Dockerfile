# ----------------------------------------------------------------
# BackEnd System Integrated Development Environment for PHP
# Version: 0.9.2
# ----------------------------------------------------------------
ARG PHP_VERSION="8"
FROM php:${PHP_VERSION}-apache
ARG USERNAME="vscode"
ARG LARAVEL_VERSION="10.*"
ARG APP_DIR="/var/www/app"
ARG PUBLIC_DIR=""
# 環境変数
ENV APACHE_RUN_GROUP=$USERNAME\
    APACHE_RUN_USER=$USERNAME\
    APP_DIR=$APP_DIR\
    DOCUMENT_ROOT="${APP_DIR}${PUBLIC_DIR}"\
    PHP_MAX_EXECUTION_TIME=30\
    PHP_MEMORY_LIMIT=128M\
    PHP_VARIABLES_ORDER=EGPCS\
    PHP_POST_MAX_SIZE=8M\
    PHP_UPLOAD_MAX_FILESIZE=2M\
    PHP_MAX_FILE_UPLOADS=20\
    XDEBUG_MODE=off\
    XDEBUG_START_WITH_REQUEST=default\
    XDEBUG_LOG_LEVEL=7\
    XDEBUG_CLIENT_HOST=localhost\
    XDEBUG_CLIENT_PORT=9003\
    XDEBUG_IDEKEY=""

# apt install
RUN apt-get update\
 && apt-get install -y --no-install-recommends\
    git\
    imagemagick\
    libfreetype6-dev\
    libjpeg-dev\
    libmagickwand-dev\
    libpng-dev\
    libwebp-dev\
    libzip-dev\
    mercurial\
    sudo\
    unzip\
    zip

# PHP Extentions Configure
RUN docker-php-ext-configure gd\
    --with-freetype\
    --with-jpeg=/usr\
    --with-webp

# PHP Extentions Install
RUN docker-php-ext-install\
    gd\
    mysqli\
    pdo_mysql\
    zip

# pecl install
RUN pecl install imagick\
 # xdebug最新はPHP7をサポートしていなので、PHP7はバージョンxdebug-3.1.6を使用
 && /bin/bash -c 'if [ "${PHP_VERSION:0:1}" == "7" ];\
    then pecl install xdebug-3.1.6;\
    else pecl install xdebug;\
    fi'

# PHP Extentions Enable
RUN docker-php-ext-enable\
    imagick\
    opcache\
    xdebug

# Composer install
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Apache module 有効化
RUN a2enmod\
    headers\
    remoteip\
    rewrite\
    expires

# Apache用ユーザ作成
RUN groupadd --gid 1000 $USERNAME \
 && useradd -s /bin/bash --uid 1000 --gid 1000 -m $USERNAME \
 && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME\
 && chmod 0440 /etc/sudoers.d/$USERNAME

# Error handling and logging 設定変更
RUN {\
    echo 'error_reporting = E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_RECOVERABLE_ERROR';\
    echo 'display_errors = Off';\
    echo 'display_startup_errors = Off';\
    echo 'log_errors = On';\
    echo 'error_log = /dev/stderr';\
    echo 'log_errors_max_len = 1024';\
    echo 'ignore_repeated_errors = On';\
    echo 'ignore_repeated_source = Off';\
    echo 'html_errors = Off';\
    } > /usr/local/etc/php/conf.d/error-logging.ini

# Resource Limits 設定変更
RUN {\
    echo 'max_execution_time = ${PHP_MAX_EXECUTION_TIME}';\
    echo 'memory_limit = ${PHP_MEMORY_LIMIT}';\
    } > /usr/local/etc/php/conf.d/resource-limits.ini

# Data Handling 設定変更
RUN {\
    echo 'variables_order = ${PHP_VARIABLES_ORDER}';\
    echo 'post_max_size = ${PHP_POST_MAX_SIZE}';\
    echo 'upload_max_filesize = ${PHP_UPLOAD_MAX_FILESIZE}';\
    echo 'max_file_uploads = ${PHP_MAX_FILE_UPLOADS}';\
    } > /usr/local/etc/php/conf.d/data-handling.ini

# xdebug 設定変更
RUN {\
    echo 'xdebug.mode=${XDEBUG_MODE}';\
    echo 'xdebug.log_level=${XDEBUG_LOG_LEVEL}';\
    echo 'xdebug.start_with_request=${XDEBUG_START_WITH_REQUEST}';\
    echo 'xdebug.client_host=${XDEBUG_CLIENT_HOST}';\
    echo 'xdebug.client_port=${XDEBUG_CLIENT_PORT}';\
    echo 'xdebug.idekey="${XDEBUG_IDEKEY}"';\
    } >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# opcache 設定変更
RUN {\
    echo 'opcache.memory_consumption=128';\
    echo 'opcache.interned_strings_buffer=8';\
    echo 'opcache.max_accelerated_files=4000';\
    echo 'opcache.revalidate_freq=2';\
    } >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

RUN cd "$APP_DIR/../"\
 && composer create-project laravel/laravel app $LARAVEL_VERSION\
 && rm -rf /var/www/html\
 && chown $USERNAME:$USERNAME -R $APP_DIR\
 && test "$DOCUMENT_ROOT" != "/var/www/html" && ln -s $DOCUMENT_ROOT /var/www/html || true
WORKDIR $APP_DIR
