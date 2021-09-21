# Money Tracker
## Benchmarking

### Enlightn
Enlightn scans your Laravel app code to provide you actionable recommendations on improving its performance, security & more.
```
# install enlightn
.docker/cmd/composer.sh require enlightn/enlightn --dev
.docker/cmd/artisan.sh vendor:publish --tag=enlightn

# run enlightn
.docker/cmd/artisan.sh enlightn --details

# remove enlightn
.docker/cmd/composer.sh remove enlightn/enlightn --dev
```

**Links:**
- https://www.laravel-enlightn.com/