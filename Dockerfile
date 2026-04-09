FROM php:8.2-apache

# Ensure only one MPM is loaded, then enable mod_rewrite
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork rewrite

# Copy app files
COPY . /var/www/html/

# Script to set Apache port from $PORT env var at runtime
RUN echo '#!/bin/bash\n\
PORT=${PORT:-80}\n\
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf\n\
sed -i "s/:80>/:$PORT>/" /etc/apache2/sites-enabled/000-default.conf\n\
apache2-foreground' > /start.sh && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
