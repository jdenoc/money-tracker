#!/usr/bin/env bash

function display_logs {
  path=$1;

  for log in $path; do
    printf "[LOG]: `basename $log`";
    if [ -f $log ]; then
      printf "\n";
      tail -500 $log;
    fi
    printf "\n-----\n\n";
  done
}

root_path="`dirname $0`/../../"

# generic laravel logs
display_logs $root_path"storage/logs/*"

# laravel dusk javascript console logs
display_logs $root_path"tests/Browser/console/*"