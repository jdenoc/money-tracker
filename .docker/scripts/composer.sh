#!/usr/bin/env bash

if [[ $(docker-compose ps application --status=running | tail -n+2 | wc -l) -eq 1 ]]; then
  # container is running
  docker-compose exec -T application composer "$@"
else
  # container NOT running
  docker-compose run --rm --no-deps -T application composer "$@"
fi