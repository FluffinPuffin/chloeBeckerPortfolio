#!/bin/bash
set -e

# Create PHP-FPM config with Unix socket
cat > /tmp/php-fpm.conf << 'FPMEOF'
[global]
error_log = /proc/self/fd/2

[www]
listen = /tmp/php-fpm.sock
listen.mode = 0666
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3
FPMEOF

# Create nginx config — blocks .db and other private files
mkdir -p /etc/nginx/conf.d
cat > /etc/nginx/conf.d/default.conf << 'NGINXEOF'
server {
    listen 80;
    root /app;
    index index.php index.html;

    location ~* \.(db|sh|toml|env|sql|log|gitignore)$ {
        deny all;
        return 404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/tmp/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO      $fastcgi_path_info;
        fastcgi_param QUERY_STRING   $query_string;
        fastcgi_param REQUEST_METHOD $request_method;
        fastcgi_param CONTENT_TYPE   $content_type;
        fastcgi_param CONTENT_LENGTH $content_length;
        fastcgi_param SCRIPT_NAME    $fastcgi_script_name;
        fastcgi_param REQUEST_URI    $request_uri;
        fastcgi_param DOCUMENT_ROOT  $document_root;
        fastcgi_param SERVER_PROTOCOL $server_protocol;
        fastcgi_param REMOTE_ADDR    $remote_addr;
        fastcgi_param SERVER_NAME    $server_name;
    }
}
NGINXEOF

# Start PHP-FPM with our config (avoids missing Nix store config error)
php-fpm --fpm-config /tmp/php-fpm.conf -D

# Start nginx
exec nginx -g 'daemon off;'
