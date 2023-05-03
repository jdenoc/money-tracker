# Money Tracker
## Benchmarking

### Enlightn
Enlightn scans your Laravel app code to provide you actionable recommendations on improving its performance, security & more.
```bash
# install enlightn
.docker/scripts/composer.sh require enlightn/enlightn --dev
.docker/scripts/artisan.sh vendor:publish --tag=enlightn

# reset cache
php artisan optimize
php artisan view:cache

# run enlightn
.docker/scripts/artisan.sh enlightn --details
# address any issues that can be addresses, then perform a baseline test
.docker/scripts/artisan.sh enlightn:baseline

# remove enlightn
.docker/scripts/composer.sh remove enlightn/enlightn --dev

# reset cache
php artisan optimize
php artisan view:cache
```

**Links:**
- https://www.laravel-enlightn.com/