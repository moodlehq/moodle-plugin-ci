# Introduction

The goal of this project is to facilitate the running of tests and code analysis tools against a Moodle plugin in
[Travis CI](https://travis-ci.org). This project uses [Phing](https://www.phing.info) as a build tool to execute the
various steps required for CI.

Supported tests and code analysis tools:
* [PHPUnit](https://phpunit.de)
* [Behat](http://behat.org/)
* [Moodle Code Checker](https://github.com/moodlehq/moodle-local_codechecker)
* PHP Linting
* [PHP Copy/Paste Detector](https://github.com/sebastianbergmann/phpcpd)
* [PHP Mess Detector](http://phpmd.org)
* [CSS Lint](https://github.com/CSSLint/csslint)
* [JSHint](http://www.jshint.com/)
* [Shifter](https://docs.moodle.org/dev/YUI/Shifter)

Requirements:
* PHP 5.4 or later.
* Moodle 2.9 or later.

# Getting started

Follow these steps to get your Moodle plugin building in Travis CI.

## Step 1

Sign into [Travis CI](https://travis-ci.org) with your Github account. Once you’re signed in, and Travis CI will have
synchronized your repositories from GitHub.  Go to your [profile](https://travis-ci.org/profile) page and enable Travis CI
for the plugin you want to build.  Now whenever your plugin receives an update or gets a new pull request, Travis CI will
run a build to make sure nothing broke.

## Step 2

Add a `.travis.yml` to your plugin.  Copy the [.travis.dist.yml](.travis.dist.yml) into the root of your
plugin with the name of `.travis.yml`.  Then make the following mandatory changes to your `.travis.yml` file:
* Replace `mod_example` with the component name of your plugin.
* Replace `mod/example` with the installation directory of your plugin.

Once you have added the `.travis.yml` file, commit and push up to Github, to trigger a Travis CI build. Check the
[build status](https://travis-ci.org/repositories) page to see if your build passes or fails.

## Step 3

This step is optional, but recommended as it greatly reduces [Composer](https://getcomposer.org) install times by making sure
that Composer does not exceed Github's API rate limits.  Follow these steps:

1. [Create a access token](https://help.github.com/articles/creating-an-access-token-for-command-line-use/) in Github.  When
   creating your access token, be sure to **uncheck ALL scopes** as this will give read-only access to public information.
2. After you have created the access token, Github gives you the opportunity to copy it to your clipboard.  Do this now.
3. Go to [Travis CI](https://travis-ci.org/repositories) and select your plugin's repository.  Then go to _Settings_ and
   then to _Environment Variables_.
4. Click on _Add a new variable_.
5. For _Value_ paste in your Github access token.
6. For the _Name_ enter `GITHUB_API_TOKEN`
7. Ensure that the _Display value in build logs_ is set to **OFF**
8. Click _Add_.

The [.travis.dist.yml](.travis.dist.yml) file has a line in it that configures Composer with your `GITHUB_API_TOKEN`.

# Travis CI file explained

Below is the [.travis.dist.yml](.travis.dist.yml) file but with comments added to explain what each section is doing.
For additional help, see [Travis CI's documentation](http://docs.travis-ci.com/user/getting-started/).

```yaml
# This is the language of our project.
language: php

# Determines which versions of PHP to test our project against.  Each version listed
# here will create separate build and run our tests against that version of PHP.
php:
 - 5.4
 - 5.5
 - 5.6
 - 7.0

# This allows PHP 7.0 to fail without failing the whole build.  This is handy for spotting
# future compatibility problems.
matrix:
 allow_failures:
  - php: 7.0

# This section sets up the environment variables for the build.
env:
 global:
# This line must be updated for your plugin!  It is the Moodle component name of your plugin.
  - COMPONENT=mod_example
# This line must be updated for your plugin!  It is the directory of where your plugin is
# installed into Moodle.
  - COMPONENT_DIR=mod/example
# This line determines which version of Moodle to test against.  You could use a matrix to
# test against multiple versions of Moodle.
  - MOODLE_BRANCH=MOODLE_29_STABLE
# This matrix is used for testing against multiple databases.  So for each version of
# PHP being tested, one will be created for each database listed here.  EG: for
# PHP 5.4, one build will be created using PHP 5.4 and pgsql.  In addition, another
# build will be created using PHP 5.4 and mysqli. 
 matrix:
  - DB=pgsql
  - DB=mysqli

# This tells Travis CI to use its newer architecture.  Everything is better!
sudo: false

# This lists steps that are run before the installation step. 
before_install:
# This configures Composer with your Github access token if you configured that in
# Travis CI.  If you didn't configure this, then it can be removed.
- composer config -g github-oauth.github.com $GITHUB_API_TOKEN

# This lists steps that are run for installation and setup.
install:
# Currently we are inside of the clone of your repository.  We move up two
# directories to build the project.
  - cd ../..
# Install our CI helper.
  - git clone git://github.com/mrmark/moodle-travis-plugin helper
  - composer install --working-dir helper
# Run the default install.  This will do the following things:
#    - Clone the Moodle project into a directory called moodle.
#    - Create things to get Moodle running: data directories,
#      config.php, a database, etc.
#    - Copy your plugin into Moodle.
#    - If your plugin has a tests directory, then PHPUnit will be setup.
#    - If your plugin has a tests/behat directory, then Behat will be setup.
#    - Lastly, other dependencies for tests will be installed.
  - helper/bin/phing -f helper/install.xml
# After the above step, there will be a moodle directory available to you.
# If needed, you can add additional steps if your plugin needs them.
# Example, adding another plugin:
#  - git clone --branch $MOODLE_BRANCH git://github.com/owner/moodle-mod_sample moodle/mod/sample

# This lists steps that are run for the purposes of testing.  Any of
# these steps can be re-ordered or removed to your liking.  And of
# course, you can add any of your own custom steps.
script:
# This step lints your PHP files to check for syntax errors.
  - helper/bin/phing -f helper/script.xml PHPLint
# This step runs the PHP Copy/Paste Detector on your plugin.
# This helps to find code duplication.
  - helper/bin/phing -f helper/script.xml PHPCPD
# This step runs the PHP Mess Detector on your plugin.
# This helps to find potential problems with your code which
# can result in refactoring opportunities.
  - helper/bin/phing -f helper/script.xml PHPMD
# This step runs the Moodle Code Checker to make sure that
# your plugin conforms to the Moodle coding standards.  It
# is highly recommended that you keep this step.
  - helper/bin/phing -f helper/script.xml CodeChecker
# This step runs CSS Lint on the CSS files in your plugin.
  - helper/bin/phing -f helper/script.xml CSSLint
# This step runs YUI Shifter on the YUI modules in your
# plugin.  This checks to make sure that the YUI modules
# have been shifted.
  - helper/bin/phing -f helper/script.xml Shifter
# This step runs JSHint on the Javascript files in your plugin.
  - helper/bin/phing -f helper/script.xml JSHint
# This step runs the PHPUnit tests of your plugin.  If your
# plugin has PHPUnit tests, then it is highly recommended
# that you keep this step.
  - helper/bin/phing -f helper/script.xml PHPUnit
# This step runs the Behat tests of your plugin.  If your
# plugin has Behat tests, then it is highly recommended
# that you keep this step.
  - helper/bin/phing -f helper/script.xml Behat
```
