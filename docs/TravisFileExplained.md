---
layout: page
title: Travis CI file explained
---

Below is the [.travis.dist.yml](https://github.com/blackboard-open-source/moodle-plugin-ci/blob/master/.travis.dist.yml)
file but with comments added to explain what each section is doing. For additional help,
see [Travis CI's documentation](http://docs.travis-ci.com/user/getting-started/).

```yaml
# This is the language of our project.
language: php

# Installs the updated version of PostgreSQL and extra APT packages.
addons:
  postgresql: "9.4"

# Ensure DB services are running.
services:
  - mysql
  - postgresql

# Cache NPM's and Composer's caches to speed up build times.
cache:
  directories:
    - $HOME/.composer/cache
    - $HOME/.npm

# Determines which versions of PHP to test our project against.  Each version
# listed here will create a separate build and run the tests against that
# version of PHP.
php:
 - 7.1
 - 7.2
 - 7.3

# This section sets up the environment variables for the build.
env:
 global:
# This line determines which version branch of Moodle to test against.
  - MOODLE_BRANCH=MOODLE_38_STABLE
# This matrix is used for testing against multiple databases.  So for
# each version of PHP being tested, one build will be created for each
# database listed here.  EG: for PHP 7.1, one build will be created
# using PHP 7.1 and pgsql.  In addition, another build will be created
# using PHP 7.1 and mysqli.
 matrix:
  - DB=pgsql
  - DB=mysqli

# Optionally, it is possible to specify a different Moodle repo to use
# (git://github.com/moodle/moodle.git is used by default):
# - MOODLE_REPO=git://github.com/username/moodle.git

# This lists steps that are run before the installation step.
before_install:
# This disables XDebug which should speed up the build.
  - phpenv config-rm xdebug.ini
# This installs NodeJS which is used by Grunt, etc.
  - nvm install 8.9
  - nvm use 8.9
# Currently we are inside of the clone of your repository.  We move up two
# directories to build the project.
  - cd ../..
# Install this project into a directory called "ci".
  - composer create-project -n --no-dev --prefer-dist blackboard-open-source/moodle-plugin-ci ci ^2
# Update the $PATH so scripts from this project can be called easily.
  - export PATH="$(cd ci/bin; pwd):$(cd ci/vendor/bin; pwd):$PATH"

# This lists steps that are run for installation and setup.
install:
# Run the default install.  The overview of what this does:
#    - Clone the Moodle project into a directory called moodle.
#    - Create a data directory called moodledata.
#    - Create Moodle config.php, database, etc.
#    - Copy your plugin(s) into Moodle.
#    - Run Composer install within Moodle.
#    - Run NPM install in Moodle and in your plugin if it has a "package.json".
#    - Run "grunt ignorefiles" within Moodle to update ignore file lists.
#    - If your plugin has Behat features, then Behat will be setup.
#    - If your plugin has unit tests, then PHPUnit will be setup.
  - moodle-plugin-ci install
# Start Selenium Standalone server with Chrome/Firefox installed. If you
# prefer to run Behat tests with Chrome profile (see Behat step details below),
# use selenium/standalone-chrome:3 image instead. If you don't run Behat tests,
# this step is not needed.
  - docker run -d -p 127.0.0.1:4444:4444 --net=host -v /dev/shm:/dev/shm -v $HOME/build/moodle:$HOME/build/moodle selenium/standalone-firefox:2.53.1
# Optional sleep interval, this is important if you run Behat tests as the first
# step after docker instance creation - in some cases Selenium server might
# not be ready yet, which will result in failure.
  - sleep 10

# This lists steps that are run for the purposes of testing.  Any of
# these steps can be re-ordered or removed to your liking.  And of
# course, you can add any of your own custom steps.
script:
# This step lints your PHP files to check for syntax errors.
  - moodle-plugin-ci phplint
# This step runs the PHP Copy/Paste Detector on your plugin.
# This helps to find code duplication.
  - moodle-plugin-ci phpcpd
# This step runs the PHP Mess Detector on your plugin. This helps to find
# potential problems with your code which can result in
# refactoring opportunities.
  - moodle-plugin-ci phpmd
# This step runs the Moodle Code Checker to make sure that your plugin
# conforms to the Moodle coding standards.  It is highly recommended
# that you keep this step.
# To fail on warnings use --max-warnings 0
  - moodle-plugin-ci codechecker
# This step runs Moodle PHPDoc checker on your plugin.
  - moodle-plugin-ci phpdoc
# This step runs some light validation on the plugin file structure
# and code.  Validation can be plugin specific.
  - moodle-plugin-ci validate
# This step validates your plugin's upgrade steps.
  - moodle-plugin-ci savepoints
# This step validates the HTML and Javascript in your Mustache templates.
  - moodle-plugin-ci mustache
# This step runs Grunt tasks on the plugin.  By default, it tries to run
# tasks relevant to your plugin and Moodle version, but you can run
# specific tasks by passing them as options,
# EG: moodle-plugin-ci grunt -t task1 -t task2
# To fail on eslint warnings use --max-lint-warnings 0
  - moodle-plugin-ci grunt
# This step runs the PHPUnit tests of your plugin.  If your plugin has
# PHPUnit tests, then it is highly recommended that you keep this step.
  - moodle-plugin-ci phpunit
# This step runs the Behat tests of your plugin.  If your plugin has
# Behat tests, then it is highly recommended that you keep this step.
# There are few important options that you may want to use:
#   - The auto rerun option allows you to rerun failures X number of times,
#     default is 2, EG usage: --auto-rerun 3
#   - The dump option allows you to print the failure HTML to the console,
#     handy for debugging, EG usage: --dump
#   - The suite option allows you to set the theme to use for behat test. If
#     not specified, the default theme is used, EG usage: --suite boost
#   - The profile option allows you to set the browser driver to use,
#     default is Firefox. If you need Chrome, set '--profile chrome' and make
#     sure that you are using correct Selenium server docker image in
#     before_install section above.
  - moodle-plugin-ci behat
```
