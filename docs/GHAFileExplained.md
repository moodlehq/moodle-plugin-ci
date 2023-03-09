---
layout: page
title: GitHub Actions CI workflow file explained
---

Below is the
[gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/master/gha.dist.yml)
file with comments added to explain what each section is doing. For additional
information please refer to [workflow syntax reference](https://docs.github.com/en/actions/reference/workflow-syntax-for-github-actions).

If you are familiar with Travis, it should be straightforward to understand
the new syntax, also you may find this [migration
manual](https://docs.github.com/en/actions/learn-github-actions/migrating-from-travis-ci-to-github-actions)
useful.

```yaml
# Title of the workflow
name: Moodle Plugin CI

# Run this workflow every time a new commit pushed to your repository or PR
# created.
on: [push, pull_request]

jobs:
  # Set the job key. The key is displayed as the job name
  # when a job name is not provided
  test:
    # Virtual environment to use.
    runs-on: ubuntu-22.04

    # DB services you need for testing.
    services:
      postgres:
        image: postgres:13
        env:
          POSTGRES_USER: 'postgres'
          POSTGRES_HOST_AUTH_METHOD: 'trust'
        ports:
          - 5432:5432
        options: --health-cmd pg_isready --health-interval 10s --health-timeout 5s --health-retries 3
      mariadb:
        image: mariadb:10
        env:
          MYSQL_USER: 'root'
          MYSQL_ALLOW_EMPTY_PASSWORD: "true"
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval 10s --health-timeout 5s --health-retries 3

    # Determines build matrix. This is a list of PHP versions, databases and
    # branches to test our project against. For each combination a separate
    # build will be created. For example below 6 builds will be created in
    # total (7.3-pgsql, 7.3-mariadb, 7.4-pgsql, 7.4-mariadb, etc.). If we add
    # another branch, total number of builds will become 12.
    # If you need to use PHP 7.0 and run phpunit coverage test, make sure you are
    # using ubuntu-16.04 virtual environment in this case to have phpdbg or
    # this version in the system. See the "Setup PHP" step below for more details
    # about PHPUnit code coverage.
    strategy:
      fail-fast: false
      matrix:
        php: ['7.3', '7.4', '8.0']
        moodle-branch: ['MOODLE_311_STABLE']
        database: [pgsql, mariadb]

    # There is an alternative way allowing to define explicitly define which php, moodle-branch
    # and database to use:
    #
    # matrix:
    #   include:
    #     - php: '8.0'
    #       moodle-branch: 'MOODLE_311_STABLE'
    #       database: pgsql
    # Optional line: Only needed if going to run php8 jobs and the plugin
    # needs xmlrpc services or other special extensions.
    #       extensions: xmlrpc-beta
    #     - php: '7.4'
    #       moodle-branch: 'MOODLE_310_STABLE'
    #       database: mariadb
    #     - php: '7.3'
    #       moodle-branch: 'MOODLE_39_STABLE'
    #       database: pgsql

    steps:
      # Check out this repository code in ./plugin directory
      - name: Check out repository code
        uses: actions/checkout@v3
        with:
          path: plugin

      # Install PHP of required version. For possible options see https://github.com/shivammathur/setup-php
      - name: Setup PHP ${{ matrix.php }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: ${{ matrix.extensions }}
          ini-values: max_input_vars=5000
          # If you are not using code coverage, keep "none". Otherwise, use "pcov" (Moodle 3.10 and up) or "xdebug".
          # If you try to use code coverage with "none", it will fallback to phpdbg (which has known problems).
          coverage: none

      # Install this project into a directory called "ci", updating PATH and
      # locale, define nvm location.
      - name: Initialise moodle-plugin-ci
        run: |
          composer create-project -n --no-dev --prefer-dist moodlehq/moodle-plugin-ci ci ^3
          echo $(cd ci/bin; pwd) >> $GITHUB_PATH
          echo $(cd ci/vendor/bin; pwd) >> $GITHUB_PATH
          sudo locale-gen en_AU.UTF-8
          echo "NVM_DIR=$HOME/.nvm" >> $GITHUB_ENV

      # Run the default install.
      # Optionally, it is possible to specify a different Moodle repo to use
      # (https://github.com/moodle/moodle.git is used by default) and define
      # ignore directives or any other env vars for install step.  For more
      # details on configuring for specific requirements please refer to the
      # 'Help' page.
      #
      # env:
      #   MOODLE_REPO=https://github.com/username/moodle.git
      #   IGNORE_PATHS: 'ignore'
      #   IGNORE_NAMES: 'ignore_name.php'
      #   MUSTACHE_IGNORE_NAMES: 'broken.mustache'
      #   CODECHECKER_IGNORE_PATHS: 'ignoreme'
      #   CODECHECKER_IGNORE_NAMES: 'ignoreme_name.php'
      - name: Install moodle-plugin-ci
        run: |
          moodle-plugin-ci install --plugin ./plugin --db-host=127.0.0.1
        env:
          DB: ${{ matrix.database }}
          MOODLE_BRANCH: ${{ matrix.moodle-branch }}

      # Steps that are run for the purpose of testing.  Any of these steps
      # can be re-ordered or removed to your liking.  And of course, you can
      # add any of your own custom steps.
      - name: PHP Lint
        if: ${{ always() }} # prevents CI run stopping if step failed.
        run: moodle-plugin-ci phplint

      - name: PHP Copy/Paste Detector
        continue-on-error: true # This step will show errors but will not fail
        if: ${{ always() }}
        run: moodle-plugin-ci phpcpd

      - name: PHP Mess Detector
        continue-on-error: true
        if: ${{ always() }}
        run: moodle-plugin-ci phpmd

      - name: Moodle Code Checker
        if: ${{ always() }}
        run: moodle-plugin-ci phpcs --max-warnings 0

      - name: Moodle PHPDoc Checker
        if: ${{ always() }}
        run: moodle-plugin-ci phpdoc

      - name: Validating
        if: ${{ always() }}
        run: moodle-plugin-ci validate

      - name: Check upgrade savepoints
        if: ${{ always() }}
        run: moodle-plugin-ci savepoints

      - name: Mustache Lint
        if: ${{ always() }}
        run: moodle-plugin-ci mustache

      - name: Grunt
        if: ${{ always() }}
        run: moodle-plugin-ci grunt --max-lint-warnings 0

      - name: PHPUnit tests
        if: ${{ always() }}
        run: moodle-plugin-ci phpunit

      - name: Behat features
        if: ${{ always() }}
        run: moodle-plugin-ci behat --profile chrome
```
