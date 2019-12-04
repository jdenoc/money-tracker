#!/usr/bin/env bash

# generic laravel logs
docker container exec -t app.money-tracker sh -c \
  "for log in ''ls -d /var/www/money-tracker/storage/logs/*''; do
    if [ -f \$log ]; then
      echo \"[LOG]: \$log\"; tail -100 \$log;
      echo \"\\n\";
    fi
  done"

# laravel dusk javascript console logs
docker container exec -t app.money-tracker sh -c \
  "for log in ''ls -d /var/www/money-tracker/tests/Browser/console/*''; do
    if [ -f \$log ]; then
      echo \"[LOG]: \$log\"; tail -100 \$log;
      echo \"\\n\";
    fi
  done"
