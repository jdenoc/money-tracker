#!/usr/bin/env bash

if [[ "$( curl -s http://selenium:4444/wd/hub/status | jq -r .value.ready )" == "true" ]]; then
  exit 0  # success
else
  exit 1  # failure
fi