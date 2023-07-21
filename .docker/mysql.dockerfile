FROM mysql:8.0

# allows stored function creators to be trusted not to create stored functions that may cause unsafe events to be written to the binary log
COPY .docker/conf/mysql-log_bin_trust.cnf /etc/mysql/conf.d/log_bin_trust.cnf

# logging
COPY .docker/conf/mysql-logging.cnf /etc/mysql/conf.d/logging.cnf

# health-check
COPY .docker/healthcheck/mysql-health-check.sh /usr/local/bin/mysql-health-check
RUN chmod +x /usr/local/bin/mysql-health-check
HEALTHCHECK --interval=5s --timeout=10s --retries=10 \
  CMD mysql-health-check