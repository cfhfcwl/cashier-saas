# syntax=docker/dockerfile:1

# ============ 阶段 1：安装 Composer 依赖 ============
# 用 php:8.2-fpm 装 composer，而不是直接用 composer:2 镜像——
# 避免"composer 镜像里的 PHP 版本"与运行时不一致，装出平台不兼容的包
FROM php:8.2-fpm AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# composer --prefer-dist 下载的 zip 包需要 unzip 解压
RUN apt-get update && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# 先只拷 composer.json / composer.lock —— 这两个文件不变时，
# 下面的依赖安装层直接命中缓存，改业务代码不会触发重新 composer install
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# --no-scripts 是必须的：TP 的 composer.json 在 post-autoload-dump
# 里跑 php think service:discover，此时 app 代码还没拷进来，会报错
COPY . .
RUN composer dump-autoload --optimize --no-dev

# ============ 阶段 2：运行环境 ============
FROM php:8.2-fpm AS runtime

# pdo_mysql：连 MySQL；opcache：生产性能；redis：限流 / 缓存
# （php 官方镜像自带编译工具链，pecl 直接装即可，首次构建会编译几分钟）
RUN docker-php-ext-install pdo_mysql opcache \
    && pecl install redis && docker-php-ext-enable redis

# php:8.2-fpm 默认时区是 UTC——不设的话所有时间字段差 8 小时，
# 表现就是"打卡记录的日期全都不对"，这种 bug 在现场极难查
RUN printf "date.timezone=Asia/Shanghai\nmemory_limit=256M\n" \
        > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

# .dockerignore 排除了 runtime/（缓存目录不该进镜像），这里重建并给 www-data 写权限
RUN mkdir -p runtime && chown -R www-data:www-data runtime

EXPOSE 9000
