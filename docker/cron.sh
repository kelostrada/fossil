#!/bin/sh
# Replaces the four per-minute crontab entries from shared hosting. Runs the
# scrape/notify cycle sequentially (no overlapping runs possible), once a minute.
set -u

cd /var/www/html

# Seed scraper state on a fresh volume — scrape.php expects these to exist.
[ -f "$STATE_DIR/page.txt" ] || echo 1 > "$STATE_DIR/page.txt"
[ -f "$STATE_DIR/type.txt" ] || echo 1 > "$STATE_DIR/type.txt"
[ -f "$STATE_DIR/last_fetched_id.txt" ] || echo 0 > "$STATE_DIR/last_fetched_id.txt"

while true; do
    php scrape.php online     || echo "scrape online failed ($?)"
    php scrape.php highscores || echo "scrape highscores failed ($?)"
    php scrape.php profiles   || echo "scrape profiles failed ($?)"
    php notify.php            || echo "notify failed ($?)"
    sleep 60
done
