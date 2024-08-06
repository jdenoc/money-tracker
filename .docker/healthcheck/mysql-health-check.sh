#!/usr/bin/env bash

mysqladmin ping --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" 2> /dev/null \
  && mysql --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --database="$MYSQL_DATABASE" --skip-column-names  -e"SELECT NOW(); SHOW TABLES;" 2> /dev/null