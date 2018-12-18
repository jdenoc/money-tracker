# Money Tracker  [![Build Status](https://travis-ci.org/jdenoc/money-tracker.svg?branch=master)](https://travis-ci.org/jdenoc/money-tracker) [![GitHub release](https://img.shields.io/github/release/jdenoc/money-tracker.svg)](https://github.com/jdenoc/money-tracker/releases/latest)

## About
Money Tracker is a web portal dedicated to help record and manage income & expenses, built on the [Laravel framework](https://laravel.com/docs/5.4)

## Features
For a list of features currently available, what they're expected outcome is and test cases, see the [Features](features/FEATURES.md)

## Topics
- [Requirements](#requirements)
- [Installation/setup](#installation)
  - [Docker](#docker-environment)
    - [Tear-down](#tear-down)
  - [Local/Dev](#local/dev-environment)
    - [Database](#dev-database)
    - [Application](#dev-application)
  - [Production](#production-environment)
    - [Database](#prod-database)
    - [Application](#prod-application)
    - [Updates](#prod-updates)
  - [Environment Variables](#environment-variable-setup)
  - [Scheduled Tasks](#scheduled-tasks-setup)
- [Testing](#testing)
  - [Travis-ci](#testing-travisci)
  - [Docker](#testing-docker)
  - [Local/Dev](#testing-locally)
- [Other Documentation](#other-documentation)

## Requirements
- NodeJS >= 6
  ```bash
  # confirm installation and version of NodeJS
  node -v
  ```
- Yarn >= 1.3.2
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
- Composer >= 1.5.1
  ```bash
    # confirm installation and version of PHP
    composer --version
    ```

***

## <a name="installation">Installation / Setup</a>
### Docker Environment

##### Host machine prep
Add `127.0.0.1  money-tracker.docker` to the host machines host file.
Host file locations can be found [here](https://en.wikipedia.org/wiki/Hosts_(file)#Location_in_the_file_system).

Obtain Host machine IP address
- Linux/Mac: `ifconfig` 
- windows: `ipconfig /all`

Set `DOCKER_HOST_IP` environment variable
- Linux/Mac: `export DOCKER_HOST_IP="192.168.x.y"`
- Windows: `setx DOCKER_HOST_IP="192.168.x.y"`

##### Clone repo
```bash
git clone https://github.com/jdenoc/money-tracker.git --branch=develop
cd money-tracker/
```

##### Run composer install  
```bash
docker/docker-composer.sh install
```

<small>***OPTIONAL***</small>:
If you're working with PhpStorm, be sure to run the following command:
```bash
docker/docker-composer.sh ide-helper
```
This will generate Laravel Facades that PhpStorm can use.  

##### Run yarn install
```bash
docker/docker-yarn.sh install
docker/docker-yarn.sh run build-dev
```

##### Bring "up" application container(s)
```bash
docker-compose -f docker/docker-compose.yml up -d
# composer doesn't right to the correct .env file
# so we need to generate the APP_KEY value again
docker container exec -t app.money-tracker artisan key:generate
```

##### Set application version value
_**Note:** you can replace_ `git describe` _with any value you want_
```bash
docker container exec -t app.money-tracker artisan app:version `git describe`
```

##### Setup database/clear existing database and re-initialise it empty
```bash
docker container exec -t app.money-tracker artisan migrate:refresh
```

##### Load dummy data into database
```bash
docker container exec -t app.money-tracker artisan migrate:refresh
docker container exec -t app.money-tracker artisan db:seed --class=UiSampleDatabaseSeeder
```

<small>***OPTIONAL***</small>:
If you have a database dump file, you can load it with this command:
```bash
docker container exec -t mysql.money-tracker mysql -u`cat .env.docker | grep DB_USERNAME | sed 's/DB_USERNAME=//'` \
	-p`cat .env.docker | grep DB_PASSWORD | sed 's/DB_PASSWORD=//'` \
	`cat .env.docker | grep DB_DATABASE | sed 's/DB_DATABASE=//'` \
	< /path/to/file.sql
```

##### Tear-down 
```bash
docker-compose -f docker/docker-compose.yml down
```

_**Note:** You can tear down the docker containers and their associated volumes with this command:_
```bash
docker-compose -f docker/docker-compose.yml down -v
```
_**Note:** You do not need to worry about "tearing down" the yarn and/or composer containers. They will "remove" themselves once they have completed their tasks._  

***

### Local/Dev environment
Sometimes, you just don't want to use Docker. That's fine and we support your decision. Here are some helpful steps on setup.

#### <a name="dev-database">Database setup</a> 
```bash
mysql -e "CREATE DATABASE money_tracker;"                                # The database can be named whatever you want. This is just an example.
mysql -e "CREATE USER 'jdenoc'@'localhost' IDENTIFIED BY 'password';"    # Once again, you can use any database username & password you want. This is just an example.
mysql -e "GRANT ALL PRIVILEGES ON money_tracker.* TO 'jdenoc'@'localhost';"
```
_**Note:** If you changed the database, user or password in the above commands, be sure to assign those new values in the .env file._

#### <a name="dev-application">Application setup</a>
```bash
# Clone repo
git clone https://github.com/jdenoc/money-tracker.git --branch=develop
cd money-tracker/

# setup composer packages & environment variables
composer install
php artisan app:version `git describe`

# ***OPTIONAL***
# If you're working with PhpStorm, be sure to run the following command.
# It will generate Laravel Facades that PhpStorm can use.
composer ide-helper

# set the application version
php artisan app:version `git describe`

# construct the database tables
php artisan migrate

# setup Yarn packages
yarn install
yarn run build-dev
```

***

### Production Environment

#### <a name="prod-database">Database Setup</a>
This is the exact same process as we do for our Local/dev setup. See instructions [here](#dev-database).

#### <a name="prod-application">Application Setup</a>
```bash
# clone & checkout the most recent git tag
git clone https://github.com/jdenoc/money-tracker.git --depth=1 --no-checkout
cd money-tracker/
git fetch --tags
MOST_RECENT_TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout -q tags/$MOST_RECENT_TAG

# setup composer packages, database tables & environment variables
cp .env.example .env
sed "s/APP_ENV=.*/APP_ENV=production/" .env
composer install --no-dev
php artisan app:version $MOST_RECENT_TAG
php artisan migrate

# setup Yarn packages
yarn install --prod
yarn run build-prod
```

***

#### <a name="prod-updates">Updates</a>
From time to time, there will be new updates released. Such updates will contain new features, bug fixes, general improvements ect. In order to allow such improvements to be usable on production deployments, you should follow these steps
```bash
# While already in the application directory, i.e.: cd money-tracker/

# put site into maintenance mode
php artisan down

git fetch --tags
MOST_RECENT_TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout -q tags/$MOST_RECENT_TAG
php artisan app:version $MOST_RECENT_TAG

# New/Updates to composer/yarn packages
# Note: check update release notes. 
composer update --no-dev
yarn install --prod
yarn run build-prod

# Database updates
# Note: check update release notes.
php artisan migrate 

# take site out of maintenance mode
php artisan up
```

***

### Environment variable setup
Be sure to edit the `.env` file generated during setup. A few of the default values should be fine to use. Modify those as needed.  
That being said, there are certainly variables that should be modified at this point. They are: 
- `APP_ENV`
- `APP_DEBUG`
- `APP_LOG_LEVEL` (_log level values can be found [here](https://github.com/Seldaek/monolog/blob/1.23.0/doc/01-usage.md#log-levels)_)
- `APP_NAME`
- `APP_URL`
- `DB_HOST`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

***

### Scheduled tasks Setup
This is most likely an item to add to your production server, but is also potentially something you'll want running in the background for you dev environment.
To set this up you will need to add the following Cron entry to your server.
```bash
* * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
```
This Cron will call the Laravel command scheduler every minute. When the `schedule:run` command is executed, Laravel will evaluate your scheduled tasks and runs the tasks that are due.  
Here is a list of commands that will _scheduled_ as part of this setup:  
- `artisan storage:clear-tmp-uploads`
- `artisan sanity-check:account-total`

***

## Testing

### <a name="testing-travisci">Travis-ci</a>
This project has been setup to use [travis-ci](https://travis-ci.org/jdenoc/money-tracker) for continuous integration testing.  

### <a name="testing-docker">Docker</a>
Assuming we already have our docker environment already setup ([instructions here](#local-docker-environment)), performing the following commands should run the tests we want.
```bash
# Run PhpUnit tests
docker container exec -t app.money-tracker vendor/bin/phpunit --stop-on-failure
# Run Dusk tests
docker container exec -t app.money-tracker artisan migrate:refresh
docker container exec -t app.money-tracker artisan db:seed --class=UiSampleDatabaseSeeder
docker container exec -t app.money-tracker artisan dusk --stop-on-failure
```

### <a name="testing-locally">Local/Dev</a>
If you wish to test locally (or on your dev environment), here are some steps to follow:
```bash
# clear existing data in database and reinstall table schemas
php artsian migrate:refresh

# run PHP unit tests
vendor/bin/phpunit --stop-on-failure

# run PHP unit tests with coverage
vendor/bin/phpunit --coverage-text --stop-on-failure

# run end-2-end Laravel Dusk tests
php artisan dusk --stop-on-failure
```

***

## Other Documentation
- [Laravel](https://laravel.com/docs/5.4/)
- [VueJS](https://vuejs.org/v2/guide/)
- [Docker](https://docs.docker.com/)
- [Composer](https://getcomposer.org/doc/)
- [Yarn](https://yarnpkg.com/en/docs)
- [PhpUnit](https://phpunit.de/documentation.html)
- [Laravel Dusk](https://laravel.com/docs/5.4/dusk)
- [Travis CI](https://docs.travis-ci.com/user/languages/php/)
- [git](https://git-scm.com/doc)