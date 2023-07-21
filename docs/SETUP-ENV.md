# Money Tracker
## Environment variable setup

Be sure to edit the `.env` file generated during setup. A few of the default values should be fine to use. Modify those as needed.  
That being said, there are certainly variables that should be modified at this point. They are:
- `APP_ENV`
- `APP_DEBUG`
- `APP_NAME`
- `APP_VERSION`
  - can be set by `php artisan app:version`
- `APP_URL`
- `DB_HOST`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `CACHE_DRIVER`
  - recommended to be `memcached`, otherwise default of `file` is fine
- `MEMCACHED_HOST`
  - only set if using `memcached` as your `CACHE_DRIVER`
- `DISCORD_WEBHOOK_URL`
