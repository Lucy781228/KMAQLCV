1. Add cron service in your `docker-compose.yml`and build again:
   
`cron:
    image: nextcloud:27
    restart: always
    volumes:
      - .data/server:/var/www/html
    entrypoint: /cron.sh
    networks:
      - app-network`
3. In the directory containing `docker-compose.yml`, create `cron.sh` and grant it execute permission:
`#!/bin/sh
echo "*/5 * * * * php -f /var/www/html/cron.php" > /etc/crontabs/www-data
crond -f -d 0`
4. Enable KMAQLCV app in `apps`.
