#!/usr/bin/env bash

OPTIONS=$1
FLAGS=

case "$OPTIONS" in
  -h|--help|?)
    echo "Laravel artisan command https://laravel.com/docs/6.x/artisan#introduction"
    echo "Usage:"
    echo "  $0 [options] {artisan command}"
    echo "Options:"
    echo "  -h|--help|?         display this \"help\" message"
    echo "  -i|--interactive    allows access to the mysql command in an interactive state"
    echo ""
    exit
  ;;
  -i|--interactive)
    # TTY interface is required to directly interact with mysql command line
    # BUT it can NOT be used if you are passing commands to or importing files to mysql.
    FLAGS=-it
    shift
    shift
  ;;
esac


docker container exec $FLAGS app.money-tracker artisan "$@"