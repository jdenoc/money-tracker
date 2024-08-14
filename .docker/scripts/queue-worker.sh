#!/usr/bin/env bash

docker compose run -T --rm --name queue-worker.money-tracker --detach application \
  artisan queue:work --tries=3 --timeout=60