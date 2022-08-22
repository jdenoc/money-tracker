FROM selenium/standalone-chrome:4.2.0

# healthcheck
COPY --chown=seluser .docker/healthcheck/selenium-health-check.sh /home/seluser/selenium-health-check
RUN chmod +x /home/seluser/selenium-health-check