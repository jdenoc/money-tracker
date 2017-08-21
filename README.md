# Money Tracker  [![Build Status](https://travis-ci.org/jdenoc/money-tracker.svg?branch=master)](https://travis-ci.org/jdenoc/money-tracker)

## About
Money Tracker is a web portal dedicated to help record and manage income & expenses, built on the [Laravel framework](https://laravel.com/docs/5.4)

## Features
For a list of features currently available, what they're expected outcome is and test cases, see the [Features](features/FEATURES.md)

## Requirements
- PHP >= 5.6.4
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

## Installation
### Database setup
```
mysql -e "CREATE DATABASE money_tracker;"   # The database can be named whatever you want. This is just an example.
mysql -e "CREATE USER tracker@localhost;"   # Once again, you can use any database username you want. This is just an example.
mysql -e "GRANT ALL PRIVILEGES ON money_tracker.* TO 'tracker'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
```

### Code deployment
```bash
git clone https://github.com/jdenoc/money-tracker.git
cd money-tracker/
composer install
# if you're working with PhpStorm, be sure to run the following commands:
php artisan ide-helper:generate
php artisan ide-helper:meta
php artisan optimize
# these will generate Laravel Facades that PhpStorm can use
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