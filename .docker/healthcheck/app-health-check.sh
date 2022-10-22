#!/usr/bin/env bash

echo "[$(date +"%Y-%m-%d %T.%6N %Z")] HEALTHCHECK" >> /var/log/php/healthcheck.log
http_code=$(curl --silent --write-out '%{http_code}' --output /dev/null http://localhost/api/version)

if [[ $http_code -eq 200 ]]; then
  exit 0  # success
else
  exit 1  # failure
fi
