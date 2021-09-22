# Money Tracker
## Testing

### GitHub Actions
This project has been set up to use [Github-Actions](https://github.com/jdenoc/money-tracker/actions) for continuous integration (CI) testing.

### Docker
Assuming we already have our docker environment already setup ([instructions here](SETUP-DOCKER.md)), performing the following commands should run the tests we want:
```bash
# Run PhpUnit tests
docker container exec -t app.money-tracker vendor/bin/phpunit --stop-on-failure
# Run Laravel Dusk tests
.docker/cmd/artisan.sh dusk --stop-on-failure
```

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