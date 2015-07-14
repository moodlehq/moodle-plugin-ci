# Introduction

This project's goal is to help simplify the process of running the tests of a Moodle in Travis CI.  This project uses
[Phing](https://www.phing.info) to run the install of Moodle and to run the various tests.

Supported tests and code analysis tools:
* PHPUnit
* Behat
* [Moodle Code Checker](https://github.com/moodlehq/moodle-local_codechecker)
* PHP Linting
* [PHP Copy/Paste Detector](https://github.com/sebastianbergmann/phpcpd)
* [PHP Mess Detector](http://phpmd.org)
* [CSS Lint](https://github.com/CSSLint/csslint)
* [JSHint](http://www.jshint.com/)
* [Shifter](https://docs.moodle.org/dev/YUI/Shifter)

# Getting started

First you need to add a `.travis.yml` to your plugin.  The contents of the `.travis.yml` file should look similar to the following: 

```yaml
language: php

php:
 - 5.4
 - 5.5
 - 5.6
 - 7.0

matrix:
 allow_failures:
  - php: 7.0

env:
 global:
  - COMPONENT=mod_example
  - COMPONENT_DIR=mod/example
  - MOODLE_BRANCH=MOODLE_29_STABLE
 matrix:
  - DB=pgsql
  - DB=mysqli

sudo: false

before_install:
- composer config -g github-oauth.github.com $GITHUB_API_TOKEN

install:
  - cd ../..
  - git clone git://github.com/mrmark/moodle-travis-plugin helper
  - composer install --working-dir helper
  - helper/bin/phing -f helper/install.xml

script:
  - helper/bin/phing -f helper/script.xml PHPLint
  - helper/bin/phing -f helper/script.xml PHPCPD
  - helper/bin/phing -f helper/script.xml PHPMD
  - helper/bin/phing -f helper/script.xml CodeChecker
  - helper/bin/phing -f helper/script.xml CSSLint
  - helper/bin/phing -f helper/script.xml Shifter
  - helper/bin/phing -f helper/script.xml JSHint
  - helper/bin/phing -f helper/script.xml PHPUnit
  - helper/bin/phing -f helper/script.xml Behat
```

Make the following mandatory changes:
* Replace `mod_example` with the component name of your plugin.
* Replace `mod/example` with the installation directory of your plugin.
* Replace `MOODLE_29_STABLE` with the version of Moodle you want to use.  This project only works with Moodle 2.9 or later.

Once you have added the `.travis.yml` file and pushed that up to your project, login to [Travis CI](https://travis-ci.org) with
your Github credentials.  Give Travis CI access to your public repository and it should do the rest.  Now whenever your plugin
receives an update or gets a new pull request, Travis CI will run a build.

Optional, but recommended for build speed,
[create a access token](https://help.github.com/articles/creating-an-access-token-for-command-line-use/) in Github to prevent
[Composer](https://getcomposer.org) from exceeding Github's API rate limit.  When creating your access token, be sure to **uncheck 
ALL scopes** as this will give read-only access to public information.  After you have created the access token and copied it to your 
clipboard, go back to your project in Travis CI.  Go to _Settings_ and then to _Environment Variables_. Click on _Add a new
variable_. For the _Name_ enter `GITHUB_API_TOKEN`, for _Value_ enter in your Github access token and ensure that the _Display
value in build logs_ is set to **OFF**.  Then click _Add_.

# Customizing the install

The initial install is done by `helper/bin/phing -f helper/install.xml` which creates a `moodle` directory and copies your plugin
into it.  It also sets up other dependencies like Moodle Code Checker or Behat testing environment.  There are other Phing targets
that you can use for the install, review the [install XML file](install.xml) for available targets.  For example, you only ever want to install
PHPUnit, you could do `helper/bin/phing -f helper/install.xml InstallPHPUnitOnly`.  Generally though, the default is best.

If you have make additional adjustments in order for your plugin to install correctly, then you can add those to the end of the
`install` section of the `.travis.yml` file.  For example, you need to add another plugin:

```yaml
install:
  # Not showing the other steps, but they are still needed!
  - git clone --branch $MOODLE_BRANCH git://github.com/owner/moodle-mod_sample moodle/mod/sample
```

# Customizing the tests

The tests and code analysis are run with `helper/bin/phing -f helper/script.xml`.  The available Phing targets that you can use in
the `script` section of your `.travis.yml` file are in the [script XML file](script.xml).  It is highly recommended that
you at least run the `PHPUnit`, `Behat` and `CodeChecker` targets if relevant to your plugin.  This will help prevent failures
when other users add your plugin to their Moodle install and run PHPUnit and Behat tests.  In addition, it makes sure your code is
in compliance with Moodle's coding standards.

Of course, feel free to add anything else you would like to run that is outside of this project.

# Other customizations

Feel free to customize the rest of your `.travis.yml` file to your liking.  For help, see Travis CI's
[documentation](http://docs.travis-ci.com/user/getting-started/).
