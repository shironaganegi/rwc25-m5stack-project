# Dockerイメージを指定
FROM php:8.2-fpm

# 必要なパッケージとPHP拡張機能のインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# PHPの拡張機能をインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# アプリケーションディレクトリを作成
WORKDIR /var/www/html

# アプリケーションファイルをコピー
COPY . /var/www/html

# composer.jsonが存在する場合のみcomposer installを実行
RUN if [ -f "composer.json" ]; then \
    composer install --no-interaction --no-dev --optimize-autoloader; \
    fi

# 適切な権限を設定
RUN chown -R www-data:www-data /var/www/html
RUN mkdir -p /var/www/html/storage && chmod -R 755 /var/www/html/storage

# PHPの設定を調整（必要に応じて）
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

EXPOSE 8000
CMD ["php-fpm"]
