## Installation


```sh
composer run-script post-create-project-cmd && php artisan migrate
```

# Docker

## Run docker image
```sh
docker-compose up --build  --force-recreate
```

## Stop docker image and remove all containers
```sh
docker-compose down --rmi all
```


## Clean up all docker images
Note: This will delete all docker images. Do not run in production.

```sh
for /F %i in ('docker images -a -q') do docker rmi -f %i
docker system prune -a -f
docker volume ls -f dangling=true
```
