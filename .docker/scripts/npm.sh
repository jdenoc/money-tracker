#!/usr/bin/env bash

docker container run --rm -t --workdir /code --volume "$PWD":/code node:18 npm "$@"