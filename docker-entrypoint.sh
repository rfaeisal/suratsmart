#!/bin/sh
PORT="${PORT:-80}"

sed -i "s/listen 80;/listen ${PORT};/" /etc/nginx/sites-enabled/default

php-fpm -D
nginx -g 'daemon off;'
