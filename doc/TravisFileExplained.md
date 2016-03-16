# Travis CI file explained

Below is the [.travis.dist.yml](../.travis.dist.yml) file but with comments added to explain what each section is doing.
For additional help, see [Travis CI's documentation](http://docs.travis-ci.com/user/getting-started/).

```yaml
# This is the language of our project.
language: php

# This tells Travis CI to use its new architecture.  Everything is better!
sudo: false

# This tells Travis CI to cache Composer's cache.  Speeds up build times.
cache:
  directories:
    - $HOME/.composer/cache

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
# Currently we are inside of the clone of your repository.  We move up two
# directories to build the project.
  - cd ../..
# Update Composer.
  - composer selfupdate
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
# This step runs CSS Lint on the CSS files in your plugin.
  - moodle-plugin-ci csslint
# This step runs YUI Shifter on the YUI modules in your plugin.  This also checks to make
# sure that the YUI modules have been shifted.
  - moodle-plugin-ci shifter
# This step runs JSHint on the Javascript files in your plugin.
  - moodle-plugin-ci jshint
# This step runs some light validation on the plugin file structure and code.  Validation can be plugin specific.
  - moodle-plugin-ci validate
# This step runs the PHPUnit tests of your plugin.  If your plugin has PHPUnit tests,
# then it is highly recommended that you keep this step.
  - moodle-plugin-ci phpunit
# This step runs the Behat tests of your plugin.  If your plugin has Behat tests, then
# it is highly recommended that you keep this step.
  - moodle-plugin-ci behat
```
