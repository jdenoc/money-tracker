# Money Tracker
## Local/Dev Environment Setup

Sometimes, you just don't want to use Docker. That's fine. We support your decision. Here are some helpful steps for the setup.

### Application setup
```bash
# Clone repo
git clone git@github.com:jdenoc/money-tracker.git --branch=develop
cd money-tracker/

# setup composer packages & environment variables
cp .env.example .env
composer install
php artisan app:version `git describe --always`

# ***OPTIONAL***
# If you're working with PhpStorm, be sure to run the following command.
# It will generate Laravel Facades that PhpStorm can use.
composer run-script ide-helper

# setup Yarn packages
yarn install
yarn run build-dev
```

***

### Database setup
```bash
mysql -e "CREATE DATABASE money_tracker;"
mysql -e "CREATE DATABASE $(grep DB_DATABASE .env | sed 's/DB_DATABASE=//');"
mysql -e "CREATE USER '$(grep DB_USERNAME .env | sed 's/DB_USERNAME=//')'@'localhost' IDENTIFIED BY '$(grep DB_PASSWORD .env | sed 's/DB_PASSWORD=//')';"
mysql -e "CREATE USER 'jdenoc'@'localhost' IDENTIFIED BY 'password';"
mysql -e "GRANT ALL PRIVILEGES ON money_tracker.* TO 'jdenoc'@'localhost';"
mysql -e "GRANT ALL PRIVILEGES ON $(grep DB_DATABASE .env | sed 's/DB_DATABASE=//').* TO '$(grep DB_USERNAME .env | sed 's/DB_USERNAME=//')'@'localhost';"
php artisan migrate:fresh
```
_**Note:** If you changed the database, user or password in the above commands, be sure to assign those new values in the .env file.
Instructions on setting that up can be found [here](SETUP-ENV.md)._
