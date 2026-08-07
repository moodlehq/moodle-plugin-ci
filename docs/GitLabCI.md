---
layout: page
title: GitLab CI example
---

This page shows a starting point for running `moodle-plugin-ci` in GitLab
CI/CD with a prebuilt Docker image. It is useful when a plugin project wants a
shorter pipeline setup than installing PHP, Composer, Node.js, database clients
and `moodle-plugin-ci` during every job.

The example uses the community-maintained
[`ffhs/moodle-lms-plugin-ci`](https://github.com/ffhs/moodle-lms-plugin-ci)
image published on
[Docker Hub](https://hub.docker.com/r/ffhs/moodle-lms-plugin-ci). Review the
image, tags and release policy before adopting it in a production pipeline. For
stricter supply-chain controls, mirror the image into your own registry and pin
by digest.

## Example `.gitlab-ci.yml`

```yaml
stages:
  - test

variables:
  COMPOSER_ALLOW_SUPERUSER: "1"
  COMPOSER_CACHE_DIR: "$CI_PROJECT_DIR/.cache/composer"
  NPM_CONFIG_CACHE: "$CI_PROJECT_DIR/.cache/npm"
  CI_BUILD_DIR: "/tmp/moodle-plugin"
  MOODLE_BRANCH: "MOODLE_500_STABLE"

.moodle_plugin_ci:
  stage: test
  interruptible: true
  image: "ffhs/moodle-lms-plugin-ci:${PHP_VERSION}"
  parallel:
    matrix:
      - PHP_VERSION: ["8.2", "8.3", "8.4"]
  cache:
    key: "moodle-plugin-ci"
    paths:
      - .cache/composer/
      - .cache/npm/
    policy: pull-push
  before_script:
    - mkdir -p "$CI_BUILD_DIR"
    - cp -a "$CI_PROJECT_DIR/." "$CI_BUILD_DIR/"
    - moodle-plugin-ci install
  script:
    - moodle-plugin-ci phplint
    - moodle-plugin-ci phpmd
    - moodle-plugin-ci phpcs --max-warnings 0
    - moodle-plugin-ci phpdoc --max-warnings 0
    - moodle-plugin-ci validate
    - moodle-plugin-ci savepoints
    - moodle-plugin-ci mustache
    - moodle-plugin-ci grunt --max-lint-warnings 0
    - moodle-plugin-ci phpunit --fail-on-warning

mariadb:
  extends: .moodle_plugin_ci
  services:
    - name: mariadb:10
      alias: mariadb
      variables:
        MYSQL_USER: "root"
        MYSQL_ALLOW_EMPTY_PASSWORD: "true"
        MYSQL_CHARACTER_SET_SERVER: "utf8mb4"
        MYSQL_COLLATION_SERVER: "utf8mb4_unicode_ci"
  variables:
    DB: "mariadb"
    DB_HOST: "mariadb"

postgres:
  extends: .moodle_plugin_ci
  services:
    - name: postgres:15
      alias: pgsql
      variables:
        POSTGRES_USER: "postgres"
        POSTGRES_HOST_AUTH_METHOD: "trust"
  variables:
    DB: "pgsql"
    DB_HOST: "pgsql"
```

## Adapting the example

Adjust `MOODLE_BRANCH` and `PHP_VERSION` to match the Moodle versions supported
by your plugin. If your plugin has no PHPUnit coverage yet, keep the validation
steps and remove `moodle-plugin-ci phpunit --fail-on-warning` until tests are
available.

Projects that use Behat need an additional browser service, a web server
process and the relevant `MOODLE_BEHAT_*` environment variables. See the
`moodle-plugin-ci behat` options in [CLI commands and options](CLI.md) before
adding Behat to the GitLab pipeline.

If the default command set is enough for your project, the `script` section can
be shortened to:

```yaml
script:
  - moodle-plugin-ci parallel
```
