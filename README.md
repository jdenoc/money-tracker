# Money Tracker  [![Build Status](https://travis-ci.org/jdenoc/money-tracker.svg?branch=master)](https://travis-ci.org/jdenoc/money-tracker)

## About
Money Tracker is a web portal dedicated to help record and manage income & expenses, built on the [Laravel framework](https://laravel.com/)

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

## About Laravel
Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects.  

Laravel has the most extensive and thorough documentation library of any modern web application framework. The [Laravel documentation](https://laravel.com/docs/5.4) is thorough, complete, and makes it a breeze to get started learning the framework.

## License
The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).