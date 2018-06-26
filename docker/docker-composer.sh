#!/usr/bin/env bash

# Note: We're using a deprecated docker image so we can be guaranteed that we can run php 5
docker container run --rm -t --volume $PWD:/app composer/composer:php5-alpine "@$"