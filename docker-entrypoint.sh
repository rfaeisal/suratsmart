#!/bin/sh
PORT="${PORT:-80}"
echo "[entrypoint] PORT=${PORT}"

# Buat ci_sessions dulu supaya session CI3 bisa jalan sebelum /migrate
php -r "
try {
    \$pdo = new PDO(
        'mysql:host=' . getenv('MYSQLHOST') . ';port=' . (getenv('MYSQLPORT') ?: 3306) . ';dbname=' . getenv('MYSQLDATABASE'),
        getenv('MYSQLUSER'), getenv('MYSQLPASSWORD'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    \$pdo->exec('CREATE TABLE IF NOT EXISTS ci_sessions (
        id VARCHAR(128) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        timestamp INT(10) UNSIGNED DEFAULT 0 NOT NULL,
        data BLOB NOT NULL,
        PRIMARY KEY (id),
        KEY ci_sessions_timestamp (timestamp)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    echo \"[entrypoint] ci_sessions ready\n\";
} catch (Exception \$e) {
    echo \"[entrypoint] DB setup warning: \" . \$e->getMessage() . \"\n\";
}
"

# Hapus config default nginx yang mungkin konflik
rm -f /etc/nginx/sites-enabled/default /etc/nginx/conf.d/default.conf

# Tulis config ke conf.d
cat > /etc/nginx/conf.d/app.conf << NGINXCONF
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

echo "[entrypoint] nginx config written"

# Start php-fpm background
php-fpm -D
echo "[entrypoint] php-fpm started"

# Validate dan start nginx
nginx -t && echo "[entrypoint] nginx config OK"
echo "[entrypoint] starting nginx on port ${PORT}"
exec nginx -g 'daemon off;'
