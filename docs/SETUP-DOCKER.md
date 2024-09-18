# Money Tracker
## Docker Environment Setup

### Host machine prep
Add `127.0.0.1  money-tracker.test` to the host machines host file.
Host file locations can be found [here](https://en.wikipedia.org/wiki/Hosts_(file)#Location_in_the_file_system).

### Clone repo
```bash
git clone git@github.com:jdenoc/money-tracker.git --branch=master
cd money-tracker/
```

### Run composer install
```bash
.docker/scripts/composer.sh install
```

<small>***OPTIONAL***</small>:
If you're working with PhpStorm, be sure to run the following command:
```bash
.docker/scripts/composer.sh run-script ide-helper
```
This will generate Laravel Facades that PhpStorm can use.

### Run npm install
```bash
.docker/scripts/npm.sh ci
```

### Bundle JavaScript and CSS assets
```bash
.docker/scripts/npm.sh run build
```
This command will statically bundle the JavaScript and CSS assets for the application. Meaning they won't dynamically change when you make changes directly to the source files.
If you wish for changes to occur dynamically, you can run the following command:
```bash
.docker/scripts/vite-dev.sh
```

### Bring "_UP_" application container(s)
```bash
docker compose up -d
```

<small>***OPTIONAL***</small>:
If you wish to run docker without xdebug, prefix the above command with `DISABLE_XDEBUG=true`  
For Example:
```bash
DISABLE_XDEBUG=true docker compose up -d
```
`DISABLE_XDEBUG=true` is required _once_ to build the docker image. Afterwards, it is never used again.

### Set application key
composer doesn't write to the correct .env file during setup so we need to generate the `APP_KEY` value again
```bash
.docker/scripts/artisan.sh key:generate
```

### Set application version
_**Note:** you can replace_ `git describe --always` _with any value you want_
```bash
.docker/scripts/artisan.sh app:version $(git describe --always)
```

### Setup database/clear existing database and re-initialise it as empty
```bash
.docker/scripts/artisan.sh migrate:fresh
```

### Load dummy data into database
```bash
.docker/scripts/artisan.sh migrate:fresh --seeder=UiSampleDatabaseSeeder
```

### Load database backup
If you have a database dump/backup file, you can load it with this command:
```bash
.docker/scripts/mysql.sh < /path/to/file.sql
```

Alternatively, you may wish to load the dump/backup sql files by copying the mysql dump file into the `database/snapshots/` directory and running the `snapshot:load` command.
For example:
```bash
cp /path/to/file.sql database/snapshots/dump-file.sql
.docker/scripts/artisan.sh snapshot:load dump-file
```

### Tear-down
```bash
docker compose down
```

_**Note:** You can tear down the docker containers as well as their associated volumes with this command:_
```bash
docker compose down -v
```
_**Note:** You do not need to worry about "tearing down" the npm and/or composer containers. They will "remove" themselves once they have completed their tasks._
