# Money Tracker  [![Build Status](https://travis-ci.org/jdenoc/money-tracker.svg?branch=master)](https://travis-ci.org/jdenoc/money-tracker)

## About
Money Tracker is a web portal dedicated to help record and manage income & expenses, built on the [Laravel framework](https://laravel.com/docs/5.4)

## Features
For a list of features currently available, what they're expected outcome is and test cases, see the [Features](features/FEATURES.md)

## Requirements
- NodeJS >= 6
  ```bash
  # confirm installation and version of NodeJS
  node -v
  ```
- Yarn 1.3.2
  ```bash
  # confirm installation and version of Yarn
  yarn --version
  ```
- 5.6.4 <= PHP < 7
  ```bash
  # confirm installation and version of PHP
  php -v
  ```
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

## Installation
### Database setup
```bash
mysql -e "CREATE DATABASE money_tracker;"                                # The database can be named whatever you want. This is just an example.
mysql -e "CREATE USER 'jdenoc'@'localhost' IDENTIFIED BY 'password';"    # Once again, you can use any database username & password you want. This is just an example.
mysql -e "GRANT ALL PRIVILEGES ON money_tracker.* TO 'jdenoc'@'localhost';"
```

### Code deployment - development
```bash
git clone https://github.com/jdenoc/money-tracker.git --branch=develop
cd money-tracker/

# setup composer packages & environment variables
composer install
# If you're working with PhpStorm, be sure to run the following command.
# It will generate Laravel Facades that PhpStorm can use.
composer ide-helper
php artisan app:version $MOST_RECENT_TAG

# setup Yarn packages
yarn install
```

### Code deployment - production
```bash
# checkout the most recent git tag
git clone https://github.com/jdenoc/money-tracker.git --depth=1 --no-checkout
cd money-tracker/
git fetch --tags
MOST_RECENT_TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout -q tags/$MOST_RECENT_TAG

# setup composer packages & environment variables
cp .env.example .env
sed "s/APP_ENV=.*/APP_ENV=production/" .env
composer install --no-dev
php artisan app:version $MOST_RECENT_TAG

# setup NodeJS/NPM/bower packages
yarn install --prod
```

### Code deployment - updates
```bash
# while already in the application directory, i.e.: cd money-tracker/
git fetch --tags
MOST_RECENT_TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout -q tags/$MOST_RECENT_TAG
php artisan app:version $MOST_RECENT_TAG

# should it be required, i.e. new packages
# development
composer update
yarn install
# production
composer update --no-dev
yarn install --prod
```

### Environment variable setup
Be sure to edit the `.env` file generated during setup. A few of the default values should be fine to use. Modify those as needed.  
That being said, there are certainly variables that should be modified at this point. They are: 
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

### Scheduled tasks Setup
You will need to add the following Cron entry to your server.
```bash
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```
This Cron will call the Laravel command scheduler every minute. When the `schedule:run` command is executed, Laravel will evaluate your scheduled tasks and runs the tasks that are due.  
Here is a list of commands that will _scheduled_ as part of this setup:  
- `artisan storage:clear-tmp-uploads`

## Testing
This project has been setup to use [travis-ci](https://travis-ci.org/jdenoc/money-tracker) for continuous integration testing. If you wish to test locally, here are some steps to follow:
```bash
# clear existing data in database and reinstall table schemas
php artsian migrate:refresh

# run PHP unit tests
vendor/bin/phpunit

# run PHP unit tests with coverage
vendor/bin/phpunit --coverage-text

# populate database with dummy data for  manual testing
php artsian migrate:refresh
php artisan db:seed --class=UiSampleDatabaseSeeder
```