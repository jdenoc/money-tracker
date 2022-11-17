#!/usr/bin/env bash

IMAGE_TAG=composer-local
if [[ $(docker image ls -q $IMAGE_TAG | wc -l) -eq 0 ]]; then
  DOCKERFILE_PATH=$(dirname $(realpath $0))/../composer.dockerfile
  CONTEXT_PATH=$(dirname $(realpath $0))/../../
  docker build --rm --file "$DOCKERFILE_PATH" --tag $IMAGE_TAG "$CONTEXT_PATH"
fi

docker container run --rm -t --volume "$PWD":/app $IMAGE_TAG "$@"