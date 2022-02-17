#!/usr/bin/env bash

docker container run --rm -t --volume $PWD:/app composer:2.1.11 "$@"
