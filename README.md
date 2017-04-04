# Introduction

The goal of this project is to facilitate the running of tests and code analysis tools against a Moodle plugin in
[Travis CI](https://travis-ci.org).

Supported tests and code analysis tools:
* [PHPUnit](https://phpunit.de)
* [Behat](http://behat.org/)
* [Moodle Code Checker](https://github.com/moodlehq/moodle-local_codechecker)
* Mustache Linting
* Grunt tasks
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
**PHP 5.6** or later and **Moodle 3.2** or later.

In addition, the plugin being tested must have a [version.php](https://docs.moodle.org/dev/version.php) file
and `$plugin->component` must be defined within it.

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
