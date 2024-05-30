# Docker Compose Configuration for Nextcloud with Cron Service

## Steps to Add Cron Service and Enable KMAQLCV App

### 1. Update `docker-compose.yml`

Add the following service definition to your `docker-compose.yml` file:

```yaml
cron:
  image: nextcloud:27
  restart: always
  volumes:
    - .data/server:/var/www/html
  entrypoint: /cron.sh
  networks:
    - app-network
```

### 2. Create `cron.sh`

In the directory containing `docker-compose.yml`, create a file named `cron.sh` with the following content:

```sh
#!/bin/sh
echo "*/5 * * * * php -f /var/www/html/cron.php" > /etc/crontabs/www-data
crond -f -d 0
```

### 3. Grant Execute Permission to `cron.sh`

Run the following command to grant execute permission to `cron.sh`:

```sh
chmod +x cron.sh
```

### 4. Build and Start the Docker Containers

Run the following command to build and start the Docker containers:

```sh
docker-compose up -d --build
```

### 5. Enable KMAQLCV App in Nextcloud

After the containers are up and running, enable the KMAQLCV app in the Nextcloud interface.
