#!/usr/bin/env bash

function display_logs {
  path=$1;

  docker container exec -t app.money-tracker bash -c \
    "for log in ''ls -d $path''; do
      if [ -f \$log ]; then
        printf \"[LOG]: \$log\";
        printf '\n';
        tail -100 \$log;
        printf '\n';
      fi
    done"
}

root_path="/var/www/money-tracker/"

# generic laravel logs
display_logs $root_path"storage/logs/*"

# laravel dusk javascript console logs
display_logs $root_path"tests/Browser/console/*"