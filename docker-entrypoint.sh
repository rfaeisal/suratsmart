#!/bin/sh
set -e

PORT="${PORT:-80}"

echo "Starting with PORT=${PORT}"

# Tulis nginx config langsung dengan PORT yang benar
cat > /etc/nginx/sites-enabled/default << NGINXCONF
server {
    listen ${PORT};
    root /var/www/html;
    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php\$is_args\$args;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
    }

    location ~ /\. {
        deny all;
    }
}
NGINXCONF

echo "nginx config written for port ${PORT}"

# Start php-fpm background
php-fpm -D
echo "php-fpm started"

# Test nginx config
nginx -t

# Start nginx foreground
exec nginx -g 'daemon off;'
