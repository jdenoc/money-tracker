#!/usr/bin/env bash

iteration=0
max_iterations=10

while [[ $iteration -lt $max_iterations ]]; do
  if [[ "$( curl -s http://selenium:4444/wd/hub/status | jq -r .value.ready )" == "true" ]]; then
    exit 0
  else
    iteration=$(( iteration+1 ))
    sleep 1
  fi;
done

exit 1