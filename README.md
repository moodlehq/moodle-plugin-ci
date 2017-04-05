# Introduction

The goal of this project is to facilitate the running of tests and code analysis tools against a Moodle plugin in
[Travis CI](https://travis-ci.org).

Supported tests and code analysis tools:
* [PHPUnit](https://phpunit.de)
* [Behat](http://behat.org/)
* [Moodle Code Checker](https://github.com/moodlehq/moodle-local_codechecker)
* [Mustache Linting](https://docs.moodle.org/dev/Templates)
* [Grunt tasks](https://docs.moodle.org/dev/Grunt)
* [PHP Linting](https://github.com/JakubOnderka/PHP-Parallel-Lint)
* [PHP Copy/Paste Detector](https://github.com/sebastianbergmann/phpcpd)
* [PHP Mess Detector](http://phpmd.org)

[![Latest Stable Version](https://poser.pugx.org/moodlerooms/moodle-plugin-ci/v/stable)](https://packagist.org/packages/moodlerooms/moodle-plugin-ci)
[![Build Status](https://travis-ci.org/moodlerooms/moodle-plugin-ci.svg?branch=master)](https://travis-ci.org/moodlerooms/moodle-plugin-ci)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/moodlerooms/moodle-plugin-ci/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/moodlerooms/moodle-plugin-ci/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/moodlerooms/moodle-plugin-ci/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/moodlerooms/moodle-plugin-ci/?branch=master)
[![Total Downloads](https://poser.pugx.org/moodlerooms/moodle-plugin-ci/downloads)](https://packagist.org/packages/moodlerooms/moodle-plugin-ci)
[![License](https://poser.pugx.org/moodlerooms/moodle-plugin-ci/license)](https://packagist.org/packages/moodlerooms/moodle-plugin-ci)

# Requirements

The requirements for **Version 2** are **PHP 5.6** or later and **Moodle 3.2** or later.

In addition, the plugin being tested must have a [version.php](https://docs.moodle.org/dev/version.php) file
and `$plugin->component` must be defined within it.

If you need to run your plugin in earlier versions of Moodle, then please use Version 1 of this tool.  Documentation
and more information about Version 1 can be found in the [v1](https://github.com/moodlerooms/moodle-plugin-ci/tree/v1)
branch.  Please know that Version 1 is no longer getting new features and may not receive additional updates.

# Getting started

Follow these steps to get your Moodle plugin building in Travis CI.

## Step 1

Sign into [Travis CI](https://travis-ci.org) with your GitHub account. Once you’re signed in, and Travis CI will have
synchronized your repositories from GitHub.  Go to your [profile](https://travis-ci.org/profile) page and enable Travis CI
for the plugin you want to build.  Now whenever your plugin receives an update or gets a new pull request, Travis CI will
run a build to make sure nothing broke.

## Step 2

Copy the [.travis.dist.yml](.travis.dist.yml) file into the root of your plugin and rename it to `.travis.yml`. Now
might be a good time to review the `.travis.yml` contents and remove anything that is not needed.  See this
[help document](doc/TravisFileExplained.md) for an explanation about the contents of the this file. Once you have
added the `.travis.yml` file, commit and push up to GitHub, to trigger a Travis CI build. Check the
[build status](https://travis-ci.org/repositories) page to see if your build passes or fails.

## Step 3

Congratulations, you are building on Travis CI!  Next steps on your continuous build journey include:

* Reviewing the below documentation to further improve and customize your build.
* Resolve any build errors you may currently have.  Get to that ever rewarding Green Build status.
* Show off your build status by [adding the badge to your plugin's README file](https://docs.travis-ci.com/user/status-images/).
* Write new tests to increase your code coverage.
* Enjoy your favorite beverage because you no longer have to waste time manually testing your plugin!

# Upgrading

Guides to updating your plugin's `.travis.yml` file to use the latest versions of this tool.

* [Upgrade to 2.0](UPGRADE-2.0.md)

# Documentation

* [Travis CI file explained](doc/TravisFileExplained.md)
* [Add extra Moodle configs](doc/AddExtraConfig.md)
* [Add extra plugins](doc/AddExtraPlugins.md)
* [Ignoring files](doc/IgnoringFiles.md)
* [Testing a plugin against PHP7](doc/PHP7.md)
* [Generating code coverage](doc/CodeCoverage.md)

# Usage / Versioning

This project uses [Semantic Versioning](http://semver.org/) for its public API.  The public API for this project
is defined as the CLI interface of the [moodle-plugin-ci](bin/moodle-plugin-ci) script.  _Everything_ outside of this
script is considered to be private API and is not guaranteed to follow Semantic Versioning.

The commands ran via the `moodle-plugin-ci` script by default take no arguments.  It is recommended, if at all possible,
to avoid using arguments to prevent problems with future versions.  Rest assured though that if any arguments are
modified, it will be in the [change log](CHANGELOG.md) and the version will be bumped appropriately according to
Semantic Versioning.

# License

This project is licensed under the GNU GPL v3 or later.  See the [LICENSE](LICENSE) file for details.
