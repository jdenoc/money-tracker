# Money Tracker
## Production Updates

From time to time, there will be new updates released. Such updates will contain new features, bug fixes, general improvements, ect. In order to allow such improvements to be usable on production deployments, you should follow these steps:

---

#### Step 1
Put site into maintenance mode
```bash
# Navigate to the application directory, i.e.: cd money-tracker/

php artisan down
```

---

#### Step 2

Define latest tag
```bash
RELEASE_NUMBER=x.y.z
```
Where `x.y.z` is the desired release number.

Download the most recent release
```bash
curl -O money-tracker-${RELEASE_NUMBER}.tar.gz https://github.com/jdenoc/money-tracker/archive/refs/tags/${RELEASE_NUMBER}.tar.gz
tar -xzf money-tracker-${RELEASE_NUMBER}.tar.gz
```

Swap directories
```bash
OLD_DIR_PATH=/path/to/money-tracker-old.$(date +%Y%m%dT%H%M%S%Z)
NEW_DIR_PATH=/path/to/money-tracker
mv $NEW_DIR_PATH $OLD_DIR_PATH
mv money-tracker-${RELEASE_NUMBER} $NEW_DIR_PATH
```

Copy directories from old directory to new
```bash
cp -a $OLD_DIR_PATH/storage/app/attachments/* $NEW_DIR_PATH/storage/app/attachments
cp -a $OLD_DIR_PATH/storage/app/tmp-uploads/* $NEW_DIR_PATH/storage/app/tmp-uploads/
cp -a $OLD_DIR_PATH/storage/logs/*.log $NEW_DIR_PATH/storage/logs/
cp -a $OLD_DIR_PATH/database/snapshots/* $NEW_DIR_PATH/database/snapshots/
cp -a $OLD_DIR_PATH/.env $NEW_DIR_PATH/.env
cp -ar $OLD_DIR_PATH/vendor/ $NEW_DIR_PATH/
cp -ar $OLD_DIR_PATH/node_modules/ $NEW_DIR_PATH/node_modules
```

---

#### Step 3
The following steps are optional. Refer to release notes to see if any of these steps are needed.  
Otherwise, skip to [Step 4](#step-4).

#### Step 3.a <small>_(optional)_</small>
```bash
# *** OPTIONAL ***
cp .env .env.bkup
# Edit .env file
# Perform modifications described in release notes.
```

#### Step 3.b <small>_(optional)_</small>
New/Updates to composer packages
```bash
composer install --no-dev --classmap-authoritative
```

#### Step 3.c <small>_(optional)_</small>
New/Updates to npm packages
```bash
npm clean-install
```

#### Step 3.d <small>_(optional)_</small>
Database updates
```bash
php artisan migrate
```

---

#### Step 4
Build website from *.vue files
```bash
npm run build:prod
```

---

#### Step 5
Clear existing cache
```bash
php artisan optimize:clear
````

Set application version
```bash
php artisan app:version $RELEASE_NUMBER
```

Setup new cache
```bash
php artisan optimize
php artisan view:cache
php artisan event:cache
```

Re-sync schedule monitoring
```bash
php artisan schedule-monitor:sync
```

---

#### Step 6
Take site out of maintenance mode
```bash
php artisan up
```

---

#### Issues?
Having issues? Take a look at the [FAQ](FAQ.md#production-updates) for potential solutions.