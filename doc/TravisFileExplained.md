# Travis CI file explained

Below is the [.travis.dist.yml](../.travis.dist.yml) file but with comments added to explain what each section is doing.
For additional help, see [Travis CI's documentation](http://docs.travis-ci.com/user/getting-started/).

```yaml
# This is the language of our project.
language: php

# This tells Travis CI to use its new architecture.  Everything is better!
sudo: false

# Installs extra APT packages.  Java 8 is only required for Mustache command.
addons:
  apt:
    packages:
      - oracle-java8-installer
      - oracle-java8-set-default

# This tells Travis CI to cache npm's and Composer's caches.  Speeds up build times.
cache:
  directories:
    - $HOME/.composer/cache
    - $HOME/.npm

# Determines which versions of PHP to test our project against.  Each version listed
# here will create a separate build and run the tests against that version of PHP.
# WARNING, PHP7 only works in Moodle 3.0.1 or later!
php:
 - 5.4
 - 5.5
 - 5.6
 - 7.0

# This section sets up the environment variables for the build.
env:
 global:
# This line determines which version of Moodle to test against.
  - MOODLE_BRANCH=MOODLE_30_STABLE
# This matrix is used for testing against multiple databases.  So for each version of
# PHP being tested, one build will be created for each database listed here.  EG: for
# PHP 5.4, one build will be created using PHP 5.4 and pgsql.  In addition, another
# build will be created using PHP 5.4 and mysqli.
 matrix:
  - DB=pgsql
  - DB=mysqli

# This lists steps that are run before the installation step. 
before_install:
# This disables XDebug which should speed up the build.  One reason to remove this
# line is if you are trying to generate code coverage with PHPUnit.
  - phpenv config-rm xdebug.ini
# This installs the latest version of NodeJS which is used by Grunt, etc.
  - nvm install node
# Currently we are inside of the clone of your repository.  We move up two
# directories to build the project.
  - cd ../..
# Install this project into a directory called "ci".
  - composer create-project -n --no-dev --prefer-dist moodlerooms/moodle-plugin-ci ci ^1
# Update the $PATH so scripts from this project can be called easily.
  - export PATH="$(cd ci/bin; pwd):$(cd ci/vendor/bin; pwd):$PATH"

# This lists steps that are run for installation and setup.
install:
# Run the default install.  The overview of what this does:
#    - Clone the Moodle project into a directory called moodle.
#    - Create Moodle config.php, database, data directories, etc.
#    - Copy your plugin into Moodle.
#    - If your plugin has Behat features, then Behat will be setup.
#    - If your plugin has unit tests, then PHPUnit will be setup.
  - moodle-plugin-ci install

# This lists steps that are run for the purposes of testing.  Any of
# these steps can be re-ordered or removed to your liking.  And of
# course, you can add any of your own custom steps.
script:
# This step lints your PHP files to check for syntax errors.
  - moodle-plugin-ci phplint
# This step runs the PHP Copy/Paste Detector on your plugin. This helps to find
# code duplication.
  - moodle-plugin-ci phpcpd
# This step runs the PHP Mess Detector on your plugin. This helps to find potential
# problems with your code which can result in refactoring opportunities.
  - moodle-plugin-ci phpmd
# This step runs the Moodle Code Checker to make sure that your plugin conforms to the
# Moodle coding standards.  It is highly recommended that you keep this step.
  - moodle-plugin-ci codechecker
# This step runs some light validation on the plugin file structure and code.  Validation can be plugin specific.
  - moodle-plugin-ci validate
# This step validates the HTML and Javascript in your Mustache templates.
  - moodle-plugin-ci mustache
# This step runs Grunt tasks on the plugin.  By default, it tries to run tasks relevant to your plugin and Moodle
# version, but you can run specific tasks by passing them as arguments, EG: moodle-plugin-ci grunt -t task1 -t task2 
  - moodle-plugin-ci grunt
# This step runs the PHPUnit tests of your plugin.  If your plugin has PHPUnit tests,
# then it is highly recommended that you keep this step.
  - moodle-plugin-ci phpunit
# This step runs the Behat tests of your plugin.  If your plugin has Behat tests, then
# it is highly recommended that you keep this step.
  - moodle-plugin-ci behat
```
