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

#### Step 2.a <small>_(optional)_</small>
```
# *** OPTIONAL ***
cp .env .env.bkup
# Edit .env file
# Perform modifications described in release notes.
```

#### Step 2.b <small>_(optional)_</small>
```
# *** OPTIONAL ***
# New/Updates to composer packages
rm -rf vendor/
composer install --no-dev -a
```

#### Step 2.c <small>_(optional)_</small>
```
# *** OPTIONAL ***
# New/Updates to npm packages
npm ci
```

#### Step 2.d <small>_(optional)_</small>
```
# *** OPTIONAL ***
# Database updates
php artisan migrate
```

#### Step 3
```
# Build website from *.vue files
npm run-script build-prod
```

#### Step 4
```
# clear existing cache
php artisan optimize:clear

# Label the latest version
php artisan app:version $MOST_RECENT_TAG

# setup new cache
php artisan optimize
php artisan view:cache
```

#### Step 5
```
# take site out of maintenance mode
php artisan up
```

---

#### Issues?
Having issues? Take a look at the [F.A,Q,](FAQ.md#production-updates) for potential solutions.