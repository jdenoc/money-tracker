#!/usr/bin/env bash

docker-compose run --rm --no-deps application composer "$@"
