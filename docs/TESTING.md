# Money Tracker
## Testing

### GitHub Actions
This project has been set up to use [Github-Actions](https://github.com/jdenoc/money-tracker/actions) for continuous integration (CI) testing.

---

### Docker
Docker environment should be setup as ([described here](SETUP-DOCKER.md)). One setup, we will want to also start the selenium container to run Laravel Dusk tests.
```bash
docker compose --file docker-compose.yml --file .docker/docker-composer.selenium.yml up -d
```

Running unit tests can be done with this command:
```bash
.docker/scripts/artisan.sh test -v
```

Running end-to-end (e2e) tests can be done with this command:
```bash
.docker/scripts/artisan.sh dusk --stop-on-failure
```

---

### Local/Dev
If you wish to test locally (or on your dev environment), here are some steps to follow:
```bash
# clear existing data in database and reinstall table schemas
php artsian migrate:fresh

# run PHP unit tests
vendor/bin/phpunit --stop-on-failure

# run PHP unit tests with coverage
vendor/bin/phpunit --coverage-text --stop-on-failure

# run end-2-end Laravel Dusk tests
php artisan dusk --stop-on-failure
```