#!/usr/bin/env bash

http_code=$(curl --silent --write-out '%{http_code}' --output /dev/null http://localhost/api/version)

if [[ $http_code -eq 200 || $http_code -eq 204 ]]; then
  exit 0  # success
else
  exit 1  # failure
fi
