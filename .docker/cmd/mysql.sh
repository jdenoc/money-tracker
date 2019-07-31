#!/usr/bin/env bash

ENV_FILE=".env.docker"

if [[ ! -f ${ENV_FILE} ]]; then
	echo "$ENV_FILE not found"
	exit
fi

docker container exec -it mysql.money-tracker mysql \
	-u`cat $ENV_FILE | grep DB_USERNAME | sed 's/DB_USERNAME=//'` \
	-p`cat $ENV_FILE | grep DB_PASSWORD | sed 's/DB_PASSWORD=//'` \
	`cat $ENV_FILE | grep DB_DATABASE | sed 's/DB_DATABASE=//'`