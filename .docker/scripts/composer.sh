#!/usr/bin/env bash

docker-compose run --rm --no-deps -T application composer "$@"
