FROM mysql:5.6

# logging
RUN LOG_CONF=/etc/mysql/conf.d/logging.cnf \
  && LOG_DIR=/var/log/mysql \
  && echo "[mysqld]" >> $LOG_CONF \
  && echo "general_log = 1" >> $LOG_CONF \
  && echo "general_log_file = $LOG_DIR/mysql_general.log" >> $LOG_CONF \
  && echo "" >> $LOG_CONF \
  && echo "log_error = $LOG_DIR/mysql_error.log" >> $LOG_CONF \
  && echo "" >> $LOG_CONF \
  && echo "slow_query_log = 1" >> $LOG_CONF \
  && echo "slow_query_log_file = $LOG_DIR/mysql_slow.log" >> $LOG_CONF \
  && echo "log_queries_not_using_indexes = 1" >> $LOG_CONF

ADD .docker/mysql-health-check.sh /usr/local/bin/mysql-health-check
RUN chmod +x /usr/local/bin/mysql-health-check

ADD .docker/iterate-mysql-health-check.sh /usr/local/bin/iterate-mysql-health-check
RUN chmod +x /usr/local/bin/iterate-mysql-health-check