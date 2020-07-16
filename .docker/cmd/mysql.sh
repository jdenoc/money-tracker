#!/usr/bin/env bash

ENV_FILE=".env.docker"

if [[ ! -f ${ENV_FILE} ]]; then
	echo "$ENV_FILE not found"
	exit
fi

docker container exec -it mysql.money-tracker mysql \
	-u"$(grep DB_USERNAME $ENV_FILE | sed 's/DB_USERNAME=//')" \
	-p"$(grep DB_PASSWORD $ENV_FILE | sed 's/DB_PASSWORD=//')" \
	"$(grep DB_DATABASE $ENV_FILE | sed 's/DB_DATABASE=//')"