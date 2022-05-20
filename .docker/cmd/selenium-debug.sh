#!/usr/bin/env bash

if [ -z "$COMPOSE_PROJECT_NAME" ]; then
  PROJECT="docker"
else
  PROJECT=$COMPOSE_PROJECT_NAME
fi;


help(){
  echo "Usage: "
  echo "  $0 {up|down}"
  echo "  COMPOSE_PROJECT_NAME=project $0 {up|down}"
}

up(){
  # usage with VNC info can be found here:
  # https://www.pawangaria.com/post/docker/debugging-docker-container-with-realvnc/

  # get container image/tag/version number
  image_hash=$(docker-compose -f .docker/docker-compose.yml -p moneytracker ps -q selenium)
  image_version=$(docker inspect --format '{{index (split .Config.Image ":") 1}}' "$image_hash")

  # stop the selenium container
  docker-compose -f .docker/docker-compose.yml -p "${PROJECT}" stop selenium
  # start the selenium-debug container
  docker container run --rm -d \
    --name selenium-debug \
    --network ${PROJECT}_net.money-tracker \
    --link application:money-tracker.docker \
    --publish 5901:5900 \
    -v /dev/shm:/dev/shm \
    selenium/standalone-chrome-debug:$image_version

  # add SELENIUM_SERVER_URL record to .env.docker
  env_path=$(echo $0 | sed "s/$(basename $0)//")../../.env.docker
  echo "" >> "$env_path"
  echo "" >> "$env_path"
  echo "SELENIUM_SERVER_URL=http://selenium-debug:4444/wd/hub" >> $env_path

  # output some important stuff
  echo "EXPOSED PORTS:"
  docker container port selenium-debug
  echo "PASSWORD:secret"
}

down(){
  # stop selenium-debug container
  docker container stop selenium-debug
  # start the selenium container
  docker-compose -f .docker/docker-compose.yml -p "${PROJECT}" start selenium
  # remove SELENIUM_SERVER_URL record from .env.docker
  env_path=""$(echo $0 | sed "s/$(basename $0)//")../../.env.docker
  sed "s/SELENIUM_SERVER_URL=.*//" $env_path > $env_path.tmp; mv $env_path.tmp $env_path
}

case "$1" in
  up)
    up
    ;;

  down)
    down
    ;;

  *)
    help
    ;;
esac