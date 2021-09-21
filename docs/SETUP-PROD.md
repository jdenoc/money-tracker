# Money Tracker
## Production Environment

### Application Setup
```bash
# clone & checkout the most recent git tag
git clone git@github.com:jdenoc/money-tracker.git --depth=1 --no-checkout
cd money-tracker/
git fetch --tags
MOST_RECENT_TAG=$(git describe --tags $(git rev-list --tags --max-count=1))
git checkout -q tags/$MOST_RECENT_TAG

# setup composer packages, database tables & environment variables
cp .env.example .env
sed "s/APP_ENV=.*/APP_ENV=production/" .env > .env.tmp; mv .env.tmp .env
sed "s/APP_DEBUG=.*/APP_DEBUG=false/" .env > .env.tmp; mv .env.tmp .env
composer install --no-dev -a
php artisan app:version $MOST_RECENT_TAG

# setup Yarn packages
yarn install
yarn run build-prod

# setup cache
php artisan optimize
php artisan view:cache
```

### Database Setup
This is the exact same process as we do for our Local/dev setup. See instructions [here](SETUP-LOCAL.md#database-setup).

### Review Performance/Security
```bash
# install enlightn
.docker/cmd/composer.sh require enlightn/enlightn --dev
.docker/cmd/artisan.sh vendor:publish --tag=enlightn

# run enlightn
.docker/cmd/artisan.sh enlightn --details
# address any issues that can be addresses, then perform a basline test
.docker/cmd/artisan.sh enlightn:baseline

# remove enlightn
.docker/cmd/composer.sh remove enlightn/enlightn --dev
```

---

### Updates
For instructions on how to update production can be found [here](UPDATE-PROD.md)