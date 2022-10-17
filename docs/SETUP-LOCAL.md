# Money Tracker
## Local/Dev Environment Setup

Sometimes, you just don't want to use Docker. That's fine. We support your decision. Here are some helpful steps for the setup.

### Application setup
<details><summary><strong>Clone Repo</strong></summary>
<p>

```bash
git clone git@github.com:jdenoc/money-tracker.git --branch=master
cd money-tracker/
```

</p>
</details>

<details><summary><strong>Setup .env file</strong></summary>
<p>

```bash
cp .env.example .env
```

</p>
</details>

<details><summary><strong>Setup/Install composer packages</strong></summary>
<p>

```bash
composer install
```

**_OPTIONAL:_**
If you're working with PhpStorm, be sure to run the following command.
It will generate Laravel Facades that PhpStorm can use.
```bash
composer run-script ide-helper
```

</p>
</details>

<details><summary><strong>Set application version</strong></summary>
<p>

```bash
php artisan app:version `git describe --always`
```

</details>

<details><summary><strong>Setup/Install npm packages</strong></summary>
<p>

```bash
npm install
npm run-script build-dev
```

</p>
</details>

---

### Database setup
```bash
mysql -e "CREATE DATABASE money_tracker;"
mysql -e "CREATE DATABASE $(grep DB_DATABASE .env | sed 's/DB_DATABASE=//');"
mysql -e "CREATE USER '$(grep DB_USERNAME .env | sed 's/DB_USERNAME=//')'@'localhost' IDENTIFIED BY '$(grep DB_PASSWORD .env | sed 's/DB_PASSWORD=//')';"
mysql -e "CREATE USER 'jdenoc'@'localhost' IDENTIFIED BY 'password';"
mysql -e "GRANT ALL PRIVILEGES ON money_tracker.* TO 'jdenoc'@'localhost';"
mysql -e "GRANT ALL PRIVILEGES ON $(grep DB_DATABASE .env | sed 's/DB_DATABASE=//').* TO '$(grep DB_USERNAME .env | sed 's/DB_USERNAME=//')'@'localhost';"
php artisan migrate:fresh
```
_**Note:** If you changed the database, user or password in the above commands, be sure to assign those new values in the .env file.
Instructions on setting that up can be found [here](SETUP-ENV.md)._
