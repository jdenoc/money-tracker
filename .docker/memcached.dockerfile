FROM memcached:1.6

USER root
RUN apt-get update \
  && apt-get install -y netcat \
  && apt-get clean

# health-check
COPY .docker/healthcheck/memcached-health-check.sh /usr/local/bin/memcached-health-check
RUN chmod +x /usr/local/bin/memcached-health-check
HEALTHCHECK --interval=5s --timeout=10s --retries=10 \
  CMD memcached-health-check

USER memcache