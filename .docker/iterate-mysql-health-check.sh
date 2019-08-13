#!/usr/bin/env bash

iteration=0
max_iterations=10

while [[ $iteration -lt $max_iterations ]]; do
	health_check=$( mysql-health-check )
	if [[ $health_check == 'OK' ]]; then
		echo "found on iteration $iteration"
		exit 0;
	else
		iteration=$(( iteration+1 ))
		sleep 10 # wait 10 seconds
	fi;
done

echo "failed health check after $iteration iteration(s)"
exit 1