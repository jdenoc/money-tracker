FROM selenium/standalone-chrome:4.2.0

# health-check
COPY --chown=seluser .docker/healthcheck/selenium-health-check.sh /home/seluser/selenium-health-check
RUN chmod +x /home/seluser/selenium-health-check
HEALTHCHECK --timeout=10s --retries=3 \
  CMD /home/seluser/selenium-health-check