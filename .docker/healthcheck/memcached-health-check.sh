#!/bin/sh

# source: https://github.com/docker-library/memcached/issues/91#issuecomment-1733748674
echo "version" | nc -vn -w 1 127.0.0.1 11211