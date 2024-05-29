## Installation
Copy the .env.example file to .env and edit it to your needs.
```sh
cp .env.example .env
```

```sh
composer run-script post-create-project-cmd && php artisan migrate
```

# Docker compose

## Run docker image
```sh
docker-compose up --build  --force-recreate
```

## Stop docker image and remove all containers
```sh
docker-compose down --rmi all
```

### Manual Build the docker image

#### Build and run in foreground
```sh
docker build -t microweber-dev/laravel-package-server . && docker run -p 80:80 -it microweber-dev/laravel-package-server
```

#### Build and run in background
```sh
docker build -t microweber-dev/laravel-package-server . && docker run -p 80:80 -d microweber-dev/laravel-package-server
```






## Clean up all docker images
Note: This will delete all docker images. Do not run in production.

```sh
for /F %i in ('docker images -a -q') do docker rmi -f %i
docker system prune -a -f
docker volume ls -f dangling=true
docker image prune -a -f
docker volume  prune
```

## Delete unused package files
```
php artisan package-builder:delete-old-package-files
```
