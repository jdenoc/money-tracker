#!/usr/bin/env bash

ENV_FILE=".env.docker"

if [[ ! -f ${ENV_FILE} ]]; then
	echo "$ENV_FILE not found"
	exit
fi

IS_INTERACTIVE=false
OPTIONS=$1

case "$OPTIONS" in
  -h|--help)
    echo "Usage: $0 [options] | < /path/to/import/file.sql"
    echo "  -h|--help           display this \"help\" message"
    echo "  -i|--interactive    allows access to the mysql command in an interactive state"
    echo ""
    exit
  ;;
  -i|--interactive)
    echo 'y';
    IS_INTERACTIVE=true
    shift
    shift
  ;;
esac

if [ "$IS_INTERACTIVE" = true ]; then
  docker container exec -ti mysql.money-tracker mysql \
    -u"$(grep DB_USERNAME $ENV_FILE | sed 's/DB_USERNAME=//')" \
    -p"$(grep DB_PASSWORD $ENV_FILE | sed 's/DB_PASSWORD=//')" \
    "$(grep DB_DATABASE $ENV_FILE | sed 's/DB_DATABASE=//')"
else
  docker container exec -i mysql.money-tracker mysql \
    -u"$(grep DB_USERNAME $ENV_FILE | sed 's/DB_USERNAME=//')" \
    -p"$(grep DB_PASSWORD $ENV_FILE | sed 's/DB_PASSWORD=//')" \
    "$(grep DB_DATABASE $ENV_FILE | sed 's/DB_DATABASE=//')"
fi