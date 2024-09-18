# Money Tracker
## Production Environment

### Application Setup

<details><summary><strong>Download the most recent release</strong></summary>
<p>

```bash
# define latest tag
RELEASE_NUMBER=
curl -O money-tracker-${RELEASE_NUMBER}.tar.gz https://github.com/jdenoc/money-tracker/archive/refs/tags/${RELEASE_NUMBER}.tar.gz
tar -xzf money-tracker-${RELEASE_NUMBER}.tar.gz
mv money-tracker-${RELEASE_NUMBER} /path/to/money-tracker
````

</p>
</details>

<details><summary><strong>Setup environment variables</strong></summary>
<p>

```bash
cp .env.example .env
sed "s/APP_ENV=.*/APP_ENV=production/" .env > .env.tmp; mv .env.tmp .env
sed "s/APP_DEBUG=.*/APP_DEBUG=false/" .env > .env.tmp; mv .env.tmp .env
sed "s/LOG_CHANNEL=.*/LOG_CHANNEL=daily/" .env > .env.tmp; mv .env.tmp .env
sed "s/LOG_LEVEL=.*/LOG_LEVEL=warning/" .env > .env.tmp; mv .env.tmp .env
```

</p>
</details>

<details><summary><strong>Setup/install composer packages</strong></summary>
<p>

```bash
composer install --no-dev --classmap-authoritative
```

</p>
</details>

<details><summary><strong>Set application version</strong></summary>
<p>

```bash
php artisan app:version $RELEASE_NUMBER
```

</p>
</details>

<details><summary><strong>Setup/install npm packages</strong></summary>
<p>

```bash
npm clean-install
```

</p>
</details>

<details><summary><strong>Bundle JavaScript and CSS assets</strong></summary>
<p>

```bash
npm run build
```

</p>
</details>

<details><summary><strong>Setup cache</strong></summary>
<p>

It is recommended, _but not required_, that you use memcached for your caching needs.
If you do not wish to install or use memcached, then the _default_ caching system (file) will be your best bet.

**File caching:**  
No modifications required. This is set by default.

**Memcached caching:**
```bash
sed "s/CACHE_DRIVER=.*/CACHE_DRIVER=memcached/" .env > .env.tmp; mv .env.tmp .env

# Change below to whatever endpoint your memcached service is running from.
# You may also want to confirm that there is a MEMCACHED_HOST record in the .env file.
# The following command will not work without one.
sed "s/MEMCACHED_HOST=.*/MEMCACHED_HOST=127.0.0.1/" .env > .env.tmp; mv .env.tmp .env
```

**Activating cache:**
```bash
php artisan optimize
php artisan view:cache
php artisan event:cache
```

</p>
</details>

<details><summary><strong>Setup monitoring</strong></summary>
<p>

Setup schedule monitoring. Requires [tasks to be scheduled](#scheduled-task-setup).
```bash
php artisan schedule-monitor:sync
```
This will also start health-check monitoring.

</p>
</details>


---

### Database Setup
This is the exact same process as we do for our Local/dev setup. See instructions [here](SETUP-LOCAL.md#database-setup).

---

### Scheduled Task Setup
For an ongoing _healthy_ application we should run some scheduled tasks. See instructions [here](SETUP-TASKS.md).

---

### Benchmarking
Need to know the performance of your application, check [here](BENCHMARKING.md).

---

### Updates
For instructions on how to update production can be found [here](UPDATE-PROD.md).