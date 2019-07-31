#!/usr/bin/env bash

docker container exec -t app.money-tracker sh -c "for log in ''ls -d /var/www/money-tracker/storage/logs/*''; do echo \"\\n\\n[LOG]: \$log\"; tail -100 \$log; done"