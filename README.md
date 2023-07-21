# Money Tracker
![Github Actions](https://github.com/jdenoc/money-tracker/workflows/Money-tracker%20CI/badge.svg?branch=master)
[![Latest Release](https://img.shields.io/github/release/jdenoc/money-tracker.svg?style=flat-square)](https://github.com/jdenoc/money-tracker/releases/latest)
[![License](https://img.shields.io/github/license/jdenoc/laravel-app-version?style=flat-square)](LICENSE)

## About
Money Tracker is a web portal dedicated to help record and manage income & expenses, built on the [Laravel framework](https://laravel.com/docs/9.x).

## Topics
- [Requirements](#requirements)
- [Docker Setup](docs/SETUP-DOCKER.md)
  - [Start Containers](docs/SETUP-DOCKER.md#bring-up-application-containers)
  - [Tear-down](docs/SETUP-DOCKER.md#tear-down)
- [Local/Dev Setup](docs/SETUP-LOCAL.md)
  - [Application](docs/SETUP-LOCAL.md#application-setup)
  - [Database](docs/SETUP-LOCAL.md#database-setup)
- [Production Setup](docs/SETUP-PROD.md)
  - [Application](docs/SETUP-PROD.md#application-setup)
  - [Database](docs/SETUP-PROD.md#database-setup)
  - [Review Performance/Security](docs/SETUP-PROD.md#review-performancesecurity)
  - [Updates](docs/UPDATE-PROD.md)
- [Schedule tasks Setup](docs/SETUP-TASKS.md)
- [Environment Variable Setup](docs/SETUP-ENV.md)
- [Testing](docs/TESTING.md)
  - [GitHub Actions](docs/TESTING.md#github-actions)
  - [Docker](docs/TESTING.md#docker)
  - [Local/Dev](docs/TESTING.md#localdev)
- [Monitoring](docs/MONITORING.md)
- [Benchmarking](docs/BENCHMARKING.md)
- [Features/Test Cases](docs/FEATURES.md)
- [Other Documentation](#other-documentation)

---

## Requirements
- NodeJS 18
  ```bash
  # confirm installation and version of NodeJS
  node -v
  
  # confirm installation and version of npm
  npm --version
  # should be 9.x
  
  # confirm installation and version of npx
  npx --version
  # should be 9.x
  ```
- PHP 8.0
  ```bash
  # confirm installation and version of PHP
  php -v
  
  # confirm php extensions installed
  php -m
  ```
  Extensions should include:
  - BCMath PHP Extension
  - Ctype PHP Extension
  - DOM PHP Extension
  - Fileinfo PHP Extension
  - IgBinary PHP Extension
  - JSON PHP Extension
  - Memcached PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PCNTL PHP Extension
  - PCRE PHP Extension
  - PDO PHP Extension
  - PDO Mysql PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
- Composer 2.2
  ```bash
  # confirm installation and version of PHP
  composer --version
  ```

---

## Other Documentation
- [Laravel](https://laravel.com/docs/9.x/)
  - [Dusk](https://laravel.com/docs/9.x/dusk)
- [Spatie](https://spatie.be/open-source?search=&sort=-downloads)
  - [laravel-db-snapshots](https://github.com/spatie/laravel-db-snapshots)
  - [laravel-health](https://github.com/spatie/laravel-health)
  - [laravel-ignition](https://github.com/spatie/laravel-ignition)
  - [laravel-schedule-monitor](https://github.com/spatie/laravel-schedule-monitor)
- [VueJS](https://vuejs.org/v2/guide/)
- [TailwindCSS](https://tailwindcss.com/)
- [ChartJs](https://www.chartjs.org/)
- [Docker](https://docs.docker.com/)
- [Composer](https://getcomposer.org/doc/)
- [npm](https://docs.npmjs.com/cli/v9)
- [PhpUnit](https://phpunit.readthedocs.io/en/9.5/)
- [GitHub Actions](https://docs.github.com/en/actions)
- [git](https://git-scm.com/doc)
- [F.A.Q.](docs/FAQ.md)