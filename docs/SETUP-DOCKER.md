# Money Tracker
## Docker Environment Setup

### Host machine prep
Add `127.0.0.1  money-tracker.docker` to the host machines host file.
Host file locations can be found [here](https://en.wikipedia.org/wiki/Hosts_(file)#Location_in_the_file_system).

Obtain Host machine IP address
- Linux/Mac: `ifconfig`
- windows: `ipconfig /all`

Set `DOCKER_HOST_IP` environment variable
- Linux/Mac: `export DOCKER_HOST_IP="192.168.x.y"`
- Windows: `setx DOCKER_HOST_IP="192.168.x.y"`

### Clone repo
```bash
git clone git@github.com:jdenoc/money-tracker.git --branch=develop
cd money-tracker/
```

### Run composer install
```bash
.docker/cmd/composer.sh install
```

<small>***OPTIONAL***</small>:
If you're working with PhpStorm, be sure to run the following command:
```bash
.docker/cmd/composer.sh run-script ide-helper
```
This will generate Laravel Facades that PhpStorm can use.

### Run npm install
```bash
.docker/cmd/npm.sh install
.docker/cmd/npm.sh run-script build-dev
```

### Bring "_UP_" application container(s)
```bash
docker-compose -f .docker/docker-compose.yml -p "moneytracker" up -d
# composer doesn't write to the correct .env file during setup
# so we need to generate the APP_KEY value again
.docker/cmd/artisan.sh key:generate
```

<small>***OPTIONAL***</small>:
If you wish to run docker without xdebug, prefix the above command with `DISABLE_XDEBUG=true`  
For Example:
```bash
DISABLE_XDEBUG=true docker-compose -f .docker/docker-compose.yml -p "moneytracker" up -d
```
`DISABLE_XDEBUG=true` is required _once_ to build the docker image. Afterwards, it is never used again.

### Set application version value
_**Note:** you can replace_ `git describe --always` _with any value you want_
```bash
.docker/cmd/artisan.sh app:version `git describe --always`
```

### Setup database/clear existing database and re-initialise it as empty
```bash
.docker/cmd/artisan.sh migrate:fresh
```

### Load dummy data into database
```bash
.docker/cmd/artisan.sh migrate:fresh --seeder=UiSampleDatabaseSeeder
```

<small>***OPTIONAL***</small>:
If you have a database dump file, you can load it with this command:
```bash
.docker/cmd/mysql.sh < /path/to/file.sql
```
`.docker/cmd/mysql.sh -i` can be used just like the typical `mysql` command, but is run within the mysql container

### Tear-down
```bash
docker-compose -f .docker/docker-compose.yml -p "moneytracker" down
```

_**Note:** You can tear down the docker containers as well as their associated volumes with this command:_
```bash
docker-compose -f .docker/docker-compose.yml -p "moneytracker" down -v
```
_**Note:** You do not need to worry about "tearing down" the npm and/or composer containers. They will "remove" themselves once they have completed their tasks._
