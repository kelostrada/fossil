FROM php:8.3-apache

# mysqli is the only extension not bundled with the official image
# (curl, mbstring, dom — needed by simple_html_dom — ship built-in).
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Scraper state lives outside the webroot so the image stays immutable and
# `docker compose down` never loses pagination/cooldown state (mounted volume).
ENV STATE_DIR=/var/www/state
RUN mkdir -p /var/www/state && chown www-data:www-data /var/www/state

COPY docker/cron.sh /usr/local/bin/fossil-cron
RUN chmod +x /usr/local/bin/fossil-cron

COPY --chown=www-data:www-data . /var/www/html/
RUN rm -rf /var/www/html/docker

EXPOSE 80
