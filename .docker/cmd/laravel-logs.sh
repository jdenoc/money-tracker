#!/usr/bin/env bash

function display_logs {
  path=$1;

  for log in $path; do
    printf "[LOG]: $log";
    if [ -f $log ]; then
      printf "\n";
      tail -250 $log;
    fi
    printf "\n-----\n\n";
  done
}

root_path="/var/www/money-tracker/"

# generic laravel logs
display_logs $root_path"storage/logs/*"

# laravel dusk javascript console logs
display_logs $root_path"tests/Browser/console/*"