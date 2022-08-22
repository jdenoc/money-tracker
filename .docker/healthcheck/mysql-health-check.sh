#!/usr/bin/env bash

mysqladmin ping --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" \
  && mysql --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --database="$MYSQL_DATABASE" --skip-column-names  -e"SELECT NOW(); SHOW TABLES;"