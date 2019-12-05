#!/usr/bin/env bash

function display_logs {
  path=$1;
  NEWLINE=$"\n"

  docker container exec -t app.money-tracker bash -c \
    "for log in ''ls -d $path''; do
      if [ -f \$log ]; then
        echo \"[LOG]: \$log\"; tail -100 \$log;
        echo \$NEWLINE;
      fi
    done"
}

root_path="/var/www/money-tracker/"


# generic laravel logs
display_logs $root_path"storage/logs/*"

# laravel dusk javascript console logs
display_logs $root_path"tests/Browser/console/*"