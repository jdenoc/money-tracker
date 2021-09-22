# Money Tracker  
![Github Actions](https://github.com/jdenoc/money-tracker/workflows/Money-tracker%20CI/badge.svg?branch=master)
[![Latest Release](https://img.shields.io/github/release/jdenoc/money-tracker.svg?style=flat-square)](https://github.com/jdenoc/money-tracker/releases/latest)
[![License](https://img.shields.io/github/license/jdenoc/laravel-app-version?style=flat-square)](LICENSE)

## About
Money Tracker is a web portal dedicated to help record and manage income & expenses, built on the [Laravel framework](https://laravel.com/docs/6.x)

## Topics
- [Requirements](#requirements)
- [Docker Setup](docs/SETUP-DOCKER.md)
  - [Start Containers](docs/SETUP-DOCKER.md#bring-_up_-application-containers)
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
  - [Github Actions](docs/TESTING.md#github-actions)
  - [Docker](docs/TESTING.md#docker)
  - [Local/Dev](docs/TESTING.md#localdev)
- [Benchmarking](docs/BENCHMARKING.md)
- [Features/Test Cases](docs/FEATURES.md)
- [Other Documentation](#other-documentation)

---

## Requirements
- NodeJS 12
  ```bash
  # confirm installation and version of NodeJS
  node -v
  ```
- Yarn 1.9.1
  ```bash
  # confirm installation and version of Yarn
  yarn --version
  ```
- PHP 7.3
  ```bash
  # confirm installation and version of PHP
  php -v
  ```
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Mbstring PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
- Composer 2.0
  ```bash
  # confirm installation and version of PHP
  composer --version
  ```

***

## Other Documentation
- [Laravel](https://laravel.com/docs/6.x/)
- [VueJS](https://vuejs.org/v2/guide/)
- [Bulma](https://bulma.io/documentation/)
- [ChartJs](https://www.chartjs.org/)
- [Docker](https://docs.docker.com/)
- [Composer](https://getcomposer.org/doc/)
- [Yarn](https://yarnpkg.com/en/docs)
- [PhpUnit](https://phpunit.readthedocs.io/en/8.5/)
- [Laravel Dusk](https://laravel.com/docs/6.x/dusk)
- [Github Actions](https://docs.github.com/en/free-pro-team@latest/actions)
- [git](https://git-scm.com/doc)