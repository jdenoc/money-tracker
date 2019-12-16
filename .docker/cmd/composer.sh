#!/usr/bin/env bash

docker container run --rm -t --volume $PWD:/app composer:1.9 "$@"