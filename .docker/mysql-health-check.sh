#!/usr/bin/env bash

test1=$( mysqladmin ping --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" \
	2>&1 | grep -v "Warning: Using a password" )

test2=$( mysql --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --database="$MYSQL_DATABASE" --skip-column-names  -e "SELECT NOW();" \
	2>&1 | grep -v "Warning: Using a password" )

test1_ok="mysqld is alive"
test2_ok=$( date +"%Y-%m-%d %H:%M:%S" )

if [[ $test1 == "$test1_ok" ]]; then
	if [[ $test2 == "$test2_ok" ]]; then
		echo OK;
	else
		# mysql connection denied
		echo KO;
	fi
else
	# mysql host is NOT available
	echo KO;
fi