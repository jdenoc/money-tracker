FROM mysql:8.0

# allows stored function creators to be trusted not to create stored functions that may cause unsafe events to be written to the binary log
RUN LOG_BIN_TRUST_CONF=/etc/mysql/conf.d/log_bin_trust.cnf \
  && touch $LOG_BIN_TRUST_CONF \
  && echo "[mysqld]" >> $LOG_BIN_TRUST_CONF \
  && echo "log_bin_trust_function_creators = 1" >> $LOG_BIN_TRUST_CONF

# logging
RUN LOG_CONF=/etc/mysql/conf.d/logging.cnf \
  && LOG_DIR=/var/log/mysql \
  && touch $LOG_CONF \
  && echo "[mysqld]" >> $LOG_CONF \
  && echo "general_log = 1" >> $LOG_CONF \
  && echo "general_log_file = $LOG_DIR/mysql_general.log" >> $LOG_CONF \
  && echo "" >> $LOG_CONF \
  && echo "log_error = $LOG_DIR/mysql_error.log" >> $LOG_CONF \
  && echo "" >> $LOG_CONF \
  && echo "slow_query_log = 1" >> $LOG_CONF \
  && echo "slow_query_log_file = $LOG_DIR/mysql_slow.log" >> $LOG_CONF \
  && echo "log_queries_not_using_indexes = 1" >> $LOG_CONF

# health-check
COPY .docker/healthcheck/mysql-health-check.sh /usr/local/bin/mysql-health-check
RUN chmod +x /usr/local/bin/mysql-health-check
HEALTHCHECK --interval=5s --timeout=10s --retries=10 \
  CMD mysql-health-check