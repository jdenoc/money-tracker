#!/usr/bin/env bash

http_code=$(curl --silent --write-out '%{http_code}' --output /var/log/php/healthcheck.log http://localhost/api/version)

if [[ $http_code -eq 200 ]]; then
  exit 0  # success
else
  exit 1  # failure
fi
