# Money Tracker
## Production Updates

From time to time, there will be new updates released. Such updates will contain new features, bug fixes, general improvements, ect. In order to allow such improvements to be usable on production deployments, you should follow these steps:

#### Step 1
```bash
# Navigate to the application directory, i.e.: cd money-tracker/

# put site into maintenance mode
php artisan down
```

#### Step 2
```
# fetch newest version
git fetch --tags
MOST_RECENT_TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout -q tags/$MOST_RECENT_TAG
```

#### Step 3
The following steps are optional. Refer to release notes to see if any of these steps are needed.  
Otherwise, skip to [Step 4](#step-4)

#### Step 3.a <small>_(optional)_</small>
```
# *** OPTIONAL ***
cp .env .env.bkup
# Edit .env file
# Perform modifications described in release notes.
```

#### Step 3.b <small>_(optional)_</small>
```
# *** OPTIONAL ***
# New/Updates to composer packages
composer install --no-dev -a
```

#### Step 3.c <small>_(optional)_</small>
```
# *** OPTIONAL ***
# New/Updates to npm packages
npm ci
```

#### Step 3.d <small>_(optional)_</small>
```
# *** OPTIONAL ***
# Database updates
php artisan migrate
```

#### Step 4
```
# Build website from *.vue files
npm run-script build-prod
```

#### Step 5
```
# clear existing cache
php artisan optimize:clear

# Label the latest version
php artisan app:version $MOST_RECENT_TAG

# setup new cache
php artisan optimize
php artisan view:cache
```

#### Step 6
```
# take site out of maintenance mode
php artisan up
```

---

#### Issues?
Having issues? Take a look at the [FAQ](FAQ.md#production-updates) for potential solutions.