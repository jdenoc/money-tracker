# Money Tracker  [![Build Status](https://travis-ci.org/jdenoc/money-tracker.svg?branch=master)](https://travis-ci.org/jdenoc/money-tracker)

## About
Money Tracker is a web portal dedicated to help record and manage income & expenses, built on the [Laravel framework](https://laravel.com/docs/5.4)

## Features
For a list of features currently available, what they're expected outcome is and test cases, see the [Features](features/FEATURES.md)

## Requirements
- NodeJS
  ```bash
  # confirm installation and version of NodeJS
  nodejs -v
  ```
- NPM
  ```bash
  # confirm installation and version of NPM
  npm -v
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

# setup composer packages
composer install
# If you're working with PhpStorm, be sure to run the following command.
# It will generate Laravel Facades that PhpStorm can use.
composer ide-helper

# setup NodeJS/NPM/bower packages
npm install
node_modules/.bin/bower install
```

### Code deployment - production
```bash
# checkout the most recent git tag
git clone https://github.com/jdenoc/money-tracker.git --depth=1 --no-checkout
cd money-tracker/
git fetch --tags
MOST_RECENT_TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout -q tags/$MOST_RECENT_TAG

# setup composer packages
cp .env.example .env
sed "s/APP_ENV=.*/APP_ENV=production/" .env
composer install --no-dev

# setup NodeJS/NPM/bower packages
npm install --only=prod
node_modules/.bin/bower install
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