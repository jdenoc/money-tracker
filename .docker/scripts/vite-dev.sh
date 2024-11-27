#!/usr/bin/env bash

VITE_HOST=vite.money-tracker.test
VITE_PORT=40028

docker container run \
    --rm --publish ${VITE_PORT}:${VITE_PORT} --name ${VITE_HOST} \
    --network money-tracker \
    --workdir /code --volume "$PWD":/code \
    node:20 npx vite \
    --host $VITE_HOST --port $VITE_PORT
