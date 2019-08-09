FROM mysql:5.6

ADD .docker/mysql-health-check.sh /usr/local/bin/mysql-health-check
RUN chmod +x /usr/local/bin/mysql-health-check

ADD .docker/iterate-mysql-health-check.sh /usr/local/bin/iterate-mysql-health-check
RUN chmod +x /usr/local/bin/iterate-mysql-health-check