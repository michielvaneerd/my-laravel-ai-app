# syntax=docker/dockerfile:1
FROM ubuntu:24.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        gnupg \
        software-properties-common \
        unzip \
    && add-apt-repository -y ppa:ondrej/php \
    && install -d /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        nodejs \
        php8.5-bcmath \
        php8.5-cli \
        php8.5-curl \
        php8.5-fpm \
        php8.5-gd \
        php8.5-intl \
        php8.5-mbstring \
        php8.5-mysql \
        php8.5-readline \
        php8.5-sqlite3 \
        php8.5-xml \
        php8.5-zip \
    && curl -fsSL https://getcomposer.org/download/2.10.3/composer.phar \
        -o /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer \
    && npm install --global --ignore-scripts @earendil-works/pi-coding-agent \
    && rm -rf /var/lib/apt/lists/*

RUN cat > /etc/nginx/sites-available/default <<'NGINX'
server {
    listen 80 default_server;
    server_name _;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX

RUN sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' /etc/php/8.5/fpm/pool.d/www.conf

WORKDIR /var/www/html
EXPOSE 80

CMD ["sh", "-c", "php-fpm8.5 -D && exec nginx -g 'daemon off;'"]