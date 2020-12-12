#!/usr/bin/env bash

ENV_FILE=".env.docker"

if [[ ! -f ${ENV_FILE} ]]; then
	echo "$ENV_FILE not found"
	exit
fi

OPTIONS=$1
FLAGS=-i

case "$OPTIONS" in
  -h|--help|?)
    echo "Usage:"
    echo "  $0 [options]"
    echo "  echo \"show tables;\" | $0"
    echo "  $0 | < /path/to/import/file.sql"
    echo "Options:"
    echo "  -h|--help|?         display this \"help\" message"
    echo "  -i|--interactive    allows access to the mysql command in an interactive state"
    echo ""
    exit
  ;;
  -i|--interactive)
    # TTY interface is required to directly interact with mysql command line
    # BUT it can NOT be used if you are passing commands to or importing files to mysql.
    FLAGS+=t
    shift
    shift
  ;;
esac

docker container exec $FLAGS mysql.money-tracker mysql \
  -u"$(grep DB_USERNAME $ENV_FILE | sed 's/DB_USERNAME=//')" \
  -p"$(grep DB_PASSWORD $ENV_FILE | sed 's/DB_PASSWORD=//')" \
  "$(grep DB_DATABASE $ENV_FILE | sed 's/DB_DATABASE=//')"