---
layout: page
title: Change log
---

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

The format of this change log follows the advice given at [Keep a CHANGELOG](http://keepachangelog.com).

## [Unreleased]
### Changed
- ACTION SUGGESTED: Now, it's safe to 'unpin' the MariaDB version in all integrations. With MariaDB 10.6.7 and 10.7.3 already released, the existing problems are gone, so it's possible to move away from the older 10.5 version. To achieve that, just look for any use of `image: mariadb:10.5` and change it to `image: mariadb:10`. For more information, see [MDL-72131](https://tracker.moodle.org/browse/MDL-72131).

## [3.2.4] - 2022-01-17
### Changed
- Updated version of [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to v3.0.6.

## [3.2.3] - 2022-01-12
### Changed
- Updated version of [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to v3.0.5.

### Fixed
- Avoid publishing the selenium container port, not needed for `host` networking.

## [3.2.2] - 2021-12-16
### Added
- Support for subplugins in the extra-plugins directory for install.
- Support for [`coverage.php`](https://docs.moodle.org/dev/Writing_PHPUnit_tests#Check_your_coverage) files added. Previous coverage defaults only will be applied when that file is not present in the plugin.

### Changed
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.
- Updated version of [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to v3.0.4.
- Both Chrome and Firefox are back to use latest Selenium 3 versions, previously pinned because of some interim problems with them.
- GitHub [no longer supports the git:// protocol](https://github.blog/2021-09-01-improving-git-protocol-security-github/). Please change any use to `https://` instead.
- Internal, various improvements to self testing.

## [3.2.1] - 2021-07-30
### Changed
- Temporary pin Selenium standalone-chrome image to 3.141.59-20210713

## [3.2.0] - 2021-07-16
### Added
- New tool-agnostic `CI_BUILD_DIR` env. variable that can be used instead of the old `TRAVIS_BUILD_DIR` one. Note that support for the later [will be removed](https://github.com/moodlehq/moodle-plugin-ci/issues/118) at some point in the future.

### Changed
- Updated [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/master/gha.dist.yml) and
  [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/master/.travis.dist.yml)
  to use PostgreSQL 10 (Moodle 4.0 new requirement).
- ACTION REQUIRED: Existing integrations running tests with PostgreSQL now need to use version 10 or newer.
- ACTION REQUIRED: Existing integrations running tests with MariaDB must avoid using the 10.6 version and use 10.5 instead. It comes with some changes making it incompatible with Moodle default installation. To achieve that, just look for any use of `image: mariadb:10` and change it to `image: mariadb:10.5`. This is being tracked @ [MDL-72131]( https://tracker.moodle.org/browse/MDL-72131) and, once fixed, it will be possible to go back to the original image.
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.
- Updated version of [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to v3.0.2.
- Improved documentation and examples about how to ignore paths and files.

## [3.1.0] - 2021-05-14
### Added
- Support for PHP 8.0 jobs.
- ACTION REQUIRED: Small modifications are required to both Travis and GHA integrations,
  only if adding PHP 8 jobs. These changes include 1) Setting up the `max_input_vars=5000`
  PHP configuration setting, for all runs, and 2) Enabling the `xmlrpc-beta` extension
  if the plugin requires xmlrpc services, only for PHP 8 runs. See
  [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/master/gha.dist.yml) and
  [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/master/.travis.dist.yml) for more information.

### Changed
- Updated various internal dependencies and tools.
- Moved [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and
  [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) dependencies to use tagged references instead of commit ones.

## [3.0.8] - 2021-04-23
### Changed
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.
- Updated version of [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to v3.0.1.

## [3.0.7] - 2021-02-25
### Changed
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) version.
- Switched to Composer v2.

## [3.0.6] - 2021-02-08
### Fixed
- `moodle-plugin-ci grunt` should also be using `npx grunt` internally

## [3.0.5] - 2021-02-04
### Fixed
- `nvm` availability check to make it work correctly in GHA
- ACTION REQUIRED: If you are using GitHub Actions, add `NVM_DIR` definition
  in "Initialise moodle-plugin-ci" step. Without it `nvm` can't be used for
  node version switching, see the step definition at
  [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/master/gha.dist.yml)
  and add missing `NVM_DIR` line your plugin's GHA workflow file.

### Changed
- `moodle-plugin-ci add-plugin` command now uses default banch to checkout
  instead of `master` if `--branch` param is not specified..

## [3.0.4] - 2021-01-29
### Fixed
- `moodle-plugin-ci grunt` now only runs against the `yui/src` directory when configuring the YUI task.
  This resolves an issue where an "Unable to find local grunt" message was reported when code was structured in a legacy
  format. See [#46](https://github.com/moodlehq/moodle-plugin-ci/issues/46) for more details.

### Changed
- `moodle-plugin-ci phpunit` when coverage report is included, phpdbg is called with ignore memory limits param
  to avoid memory exhausted errors.
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.
- Updated version of [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to v3.0.0.
- Install grunt locally and use `npx grunt` to run it instead of installing it globally.

### Added
- Detect existence of legacy php-webdriver, and use a different Firefox image when it is in use.
- Add [manual](https://github.com/moodlehq/moodle-plugin-ci/blob/master/docs/index.md) and [example](https://github.com/moodlehq/moodle-plugin-ci/blob/master/docs/GHAFileExplained.md) on using GitHub Actions as CI tool.

## [3.0.3] - 2020-10-16
### Changed
- Updated version of [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to v2.9.8.
- Updated project dependencies ([moodle-local_ci](https://github.com/moodlehq/moodle-local_ci)).

## [3.0.2] - 2020-09-11
### Added
- Skip HTML validation in mustache templates adding a `.mustachelintignore` standard ignores file to the plugin. Useful for templates containing specific syntax not being valid HTML (Ionic..).

### Changed
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

## [3.0.1] - 2020-09-04
### Changed
- Updated [.travis.dist.yml] to use Postgresql 9.6 (Moodle 3.10 new requirement).
- Updated composer.json to use the latest version of `local_moodlecheck` plugin.
- Updated project dependencies

### Fixed
- `moodle-plugin-ci grunt` now also checks `*.js.map` files.

## [3.0.0] - 2020-07-23
### Changed
- ACTION REQUIRED: project organization renamed to moodlehq. You must update your `.travis.yml` to use `moodlehq/moodle-plugin-ci`
- ACTION REQUIRED: If you initiated Selenium server in docker container as
  part of your test scenario (e.g. separate step in install stage similar to
  one outlined in workaround
  [blackboard-open-source/issue#110](https://github.com/blackboard-open-source/moodle-plugin-ci/issues/110)),
  this is no longer required, you can remove this step.
- ACTION REQUIRED: You may safely remove `nvm install <version>` and `nvm use
  <version>` from .travis.yml, this is now a part of installation routine.
- Updated [.travis.dist.yml] with a new `services` section to ensure databases start.
- Updated [.travis.dist.yml] to remove `openjdk-8-jre-headless` and updated `moodlehq/moodle-local_ci` to fix Mustache linting. See [moodle-local_ci/pull#198](https://github.com/moodlehq/moodle-local_ci/pull/198).
- `moodle-plugin-ci behat` is using Selenium docker container for built-in Selenium server.
- Updated version of `moodlehq/moodle-local_codechecker` to v2.9.7
- Updated [.travis.dist.yml] to build Moodle 3.9
- `moodle-plugin-ci install` installs Node.js (npm) using the version
  specified in .nvmrc file or `lts/carbon` if .nvmrc is missing (pre Moodle
  3.5). It is also possible to override default version by providing
  --node-version parameter or defining `NODE_VERSION` env variable. The value of
  this parameter should be compatible with `nvm install` command,
  e.g. `v8.9`, `8.9.0`, `lts/erbium`. See
  [#7](https://github.com/moodlehq/moodle-plugin-ci/issues/7)

### Added
- New help document: [CLI commands and options](CLI.md)
- Upgrade guide: [Upgrading from 2.X to 3.0](UPGRADE-3.0.md)

### Removed
- Support for PHP 5.x (7.0.0 is now required).

## [2.5.0] - 2019-02-20
### Changed
- Updated [.travis.dist.yml] to install `openjdk-8-jre-headless` instead of
  `oracle-java8-installer` and `oracle-java8-set-default` packages. See
  [#83](https://github.com/blackboard-open-source/moodle-plugin-ci/issues/83) for details.
- Updated project dependencies: Moodle Code Checker v2.9.3 (added PHP 7.3 support)

### Added
- Add `moodle-plugin-ci phpdoc` check which executes
  [moodlehq/moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck)
  on the plugin.
- `moodle-plugin-ci behat` now provides an option `--suite` to define the
  theme to use while running the test.

## [2.4.0] - 2018-09-11
### Changed
- ACTION REQUIRED: project organization renamed to `blackboard-open-source`. You must
  update your `.travis.yml` to use `blackboard-open-source/moodle-plugin-ci`
- Updated [.travis.dist.yml] to build Moodle 3.5: removed PHP 5.6 and upgrade to Postgresql 9.4.
- Updated project dependencies.

## [2.3.0] - 2018-05-14
### Changed
- Recommending `sudo: true` when using Behat. See
  [#70](https://github.com/blackboard-open-source/moodle-plugin-ci/issues/70) for details.
- Updated project dependencies.  Most notably, Moodle Code Checker v2.7.1.

### Added
- Support for MariaDB.  To use, set `DB=mariadb` in your build.  In additon, see
  [database setup](https://docs.travis-ci.com/user/database-setup/#MariaDB)
  documentation for how to add MariaDB to your build.

## [2.2.0] - 2017-11-03
### Changed
- ACTION REQUIRED: changed the `nvm install node` to `nvm install 8.9` and `nvm use 8.9` in
  the [.travis.dist.yml] and you must update your `.travis.yml` file to get Grunt commands
  running again.  These commands match what the Moodle project is currently using.

### Fixed
- `moodle-plugin-ci validate` now only regards required language strings as present if they are assigned to the
  `$string` array. Before, other array variables were accepted although Moodle would not recognise them.  

### Added
- `moodle-plugin-ci install` now provides an option `--no-init` to skip initialization of the Behat and PHPUnit
  test suites.  Only use this option if execution of these tests are not required.

## [2.1.1] - 2017-09-29
### Fixed
- `moodle-plugin-ci validate` now properly validates all table name prefixes in the plugin's
  `db/install.xml` file.  Before, if any table name was properly prefixed, this would pass.

## [2.1.0] - 2017-09-13
### Fixed
- ACTION REQUIRED: added `firefox: "47.0.1"` to [.travis.dist.yml] and you must add it to your
  `.travis.yml` file to get Behat running again.  This is because Travis CI changed their default
  environment from Precise to Trusy.  On Trusty, the default Firefox version is 55, which is not
  compatible with Selenium.
- Fixed `moodle-plugin-ci mustache` command when `_JAVA_OPTIONS` environment variable is set.
  This is now set by default in Trusty builds.

### Added
- Can now use Chrome with Behat, see [help document](Chrome.md) for details.

### Changed
- Set password via environment when connecting with Postgres.

## [2.0.1] - 2017-06-07
### Fixed
- PHPUnit code coverage whitelist for Moodle 3.3 or later.

## [2.0.0] - 2017-06-01
### Changed
- BREAKING: requires PHP 5.6 or later.
- BREAKING: requires Moodle 3.2 or later.
- `moodle-plugin-ci codechecker` command no longer processes Javascript files.  Use the new `grunt` command instead.
- `moodle-plugin-ci codechecker` now runs the PHP Compatibility coding standard.  This will now check for
  PHP compatibility issues for the **currently** running PHP version.  This makes it important to run this command
  on your lowest and highest supported PHP version.  EG: on PHP 5.6 and 7.1.
- `moodle-plugin-ci validate` command now validates tags in Behat feature files.  EG: mod_forum should have @mod
  and @mod_forum tags in each feature file. 
- The `.travis.dist.yml` now installs Version 2 of this tool.
- Updated Moodle coding standard to v2.7.0.

### Removed
- BREAKING: removed `moodle-plugin-ci csslint` command.  Replaced with `grunt` command.
- BREAKING: removed `moodle-plugin-ci jshint` command.  Replaced with `grunt` command.
- BREAKING: removed `moodle-plugin-ci shifter` command.  Replaced with `grunt` command.
- The Composer self update step from `.travis.dist.yml`.

### Added
- Defining ignore files per command, see [help document](IgnoringFiles.md) for details.
- `moodle-plugin-ci mustache` command which lints your Mustache template files.
- `moodle-plugin-ci grunt` command which runs Grunt tasks on the plugin. See [help document](TravisFileExplained.md)
  for more details about the command.
- `moodle-plugin-ci savepoints` command which checks your plugin's upgrade steps.
- `--dump` option to `behat` command to print Behat HTML failure captures.
- `--auto-rerun` option to `behat` automatically rerun failures, defaults to 2 reruns.
- The `.travis.dist.yml` now has steps to install Java 8.
- The `.travis.dist.yml` now has steps to install latest version of NodeJS and NPM.
- The `.travis.dist.yml` now has a step to cache the NPM cache.
- The `.travis.dist.yml` now has a step to install PostgreSQL 9.3.

## [1.5.8] - 2017-03-30
### Fixed
- PHP 5.6 issue with Behat.

## [1.5.7] - 2017-02-06
### Fixed
- `moodle-plugin-ci install` when installing multiple plugins that have circular dependencies.
- Stalled Travis jobs when Behat is required.

### Changed
- The validation of the `MOODLE_BRANCH` value has been relaxed.  Can be any branch or tag.

### Added
- Can use `MOODLE_REPO` environment variable to override Moodle's git clone URL.  This is considered
  more of an advanced or debugging feature and should not need to be used often.

## [1.5.6] - 2016-10-06
### Changed
- Upgraded PHP_CodeSniffer to `2.6.2`.
- Updated Moodle coding standard with latest changes from `v2.5.4`.

## [1.5.5] - 2016-07-05
### Fixed
- `moodle-plugin-ci validate` command no longer requires `blockname:addinstance` and `blockname:myaddinstance` for
  blocks because depending on allowed formats and block class overrides, they may not actually be required.

### Changed
- `moodle-plugin-ci validate` command now has more specific requirements for repository plugins.

## [1.5.4] - 2016-05-12
### Fixed
- Regression from 1.5.3 with ignore paths and names.

## [1.5.3] - 2016-05-11
### Fixed
- When installing plugins, now install in order based on `$plugin->dependencies` definitions.
- Ignore `amd/build` directory.

## [1.5.2] - 2016-04-07
### Fixed
- Inspection bugs and a bug introduced in 1.5.1.

## [1.5.1] - 2016-04-07
### Changed
- Upgraded PHP_CodeSniffer to 2.6.0.

## [1.5.0] - 2016-04-01
### Added
- `moodle-plugin-ci add-plugin` command. Allows for installing plugin dependencies.
- `doc/AddExtraPlugins.md` documentation on how to use the `add-plugin` command.
- `doc/CodeCoverage.md` documentation on how to generate code coverage for a plugin.
- `--coverage-text` option to `phpunit` command to print text code coverage.
- `--coverage-clover` option to `phpunit` command to create a code coverage XML file.
- `moodle-plugin-ci coveralls-upload` command to upload code coverage to Coveralls.

### Fixed
- Behat command to be compatible with Behat 3 which is used in Moodle 3.1.
- The `.travis.dist.yml` file now uses the Composer `--prefer-dist` option.
- PHPUnit installer now builds component configs.
- When the `phpunit` command is run, it will prefer the component's `phpunit.xml` configuration.

## [1.4.1] - 2016-03-14
### Changed
- Updated Moodle coding standard with latest changes.

## [1.4.0] - 2016-02-12
### Changed
- Updated Moodle coding standard with latest changes.  This fully supports PHP_CodeSniffer 2+ so some new sniff
  failures might appear as they were silently hidden before.  Also includes fixes and improvements.
- Upgraded PHP_CodeSniffer to 2.5.1 to be in alignment with code checker.

## [1.3.1] - 2016-01-28
### Fixed
- Downgraded PHP_CodeSniffer to 2.5.0 from 2.5.1 to fix PHP Notices with the Moodle sniffs.

## [1.3.0] - 2016-01-28
### Removed
- PhantomJS support.  No way to actually use it and Selenium can handle everything.

### Changed
- The `.travis.dist.yml` now has the new `moodle-plugin-ci validate` command.
- The `.travis.dist.yml` file not longer allows PHP7 to fail.
- The `.travis.dist.yml` file now disables XDebug to improve build times.
- The `.travis.dist.yml` file now defaults to Moodle 3 stable which supports PHP7.  Older versions of Moodle do
  not support PHP7, so take this into account when you update your YAML file.
- Project dependencies have been updated.

### Added
- _Testing a plugin against PHP7_ help document.
- `moodle-plugin-ci validate` command. Does some light validation of plugin file structure and code.
  Validation can be plugin specific.
- `moodle-plugin-ci parallel` command. Runs all the commands at once. This command is **not** supposed to be used
  on Travis CI, but rather locally, to save programmer fingers.

## [1.2.0] - 2015-12-31
### Fixed
- Pass host when connecting with MySQL and Postgres.
- Add quotes around database name for MySQL and Postgres.

### Changed
- MySQL database collation from UTF8_bin to utf8_general_ci.
- Project dependencies have been updated for PHP7 support.

### Added
- Support for [glob](http://php.net/manual/en/function.glob.php) patterns for file paths in `thirdpartylibs.xml` files.

## [1.1.0] - 2015-10-19
### Added
- `moodle-plugin-ci phpcbf` command. Re-formats code according to Moodle coding standards. This command is **not**
  supposed to be used on Travis CI, but rather locally to fix coding style problems.

### Changed
- Commands no longer error when relevant files are not found.

## 1.0.0 - 2015-09-18
### Added
- `moodle-plugin-ci install` command.  This does all of the setup for testing.
- `moodle-plugin-ci add-config` command.  Adds extra configs to Moodle's config file.
- `moodle-plugin-ci behat` command.  Runs plugin Behat features.
- `moodle-plugin-ci phpunit` command.  Runs plugin PHPUnit tests.
- `moodle-plugin-ci phplint` command.  Lints PHP files in the plugin.
- `moodle-plugin-ci codechecker` command.  Run Moodle Code Checker on the plugin.
- `moodle-plugin-ci phpcpd` command.  Run PHP Copy/Paste Detector on the plugin.
- `moodle-plugin-ci phpmd` command.  Run PHP Mess Detector on the plugin.
- `moodle-plugin-ci jshint` command.  Run JSHint on the Javascript files in the plugin.
- `moodle-plugin-ci shifter` command.  Run YUI Shifter on plugin YUI modules.
- `moodle-plugin-ci csslint` command.  Lints the CSS files in the plugin.

[Unreleased]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.2.4...master
[3.2.4]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.2.3...3.2.4
[3.2.3]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.2.2...3.2.3
[3.2.2]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.2.1...3.2.2
[3.2.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.1.0...3.2.0
[3.1.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.8...3.1.0
[3.0.8]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.7...3.0.8
[3.0.7]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.6...3.0.7
[3.0.6]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.5...3.0.6
[3.0.5]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.4...3.0.5
[3.0.4]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.3...3.0.4
[3.0.3]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.2...3.0.3
[3.0.2]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/2.5.0...3.0.0
[2.5.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/2.4.0...2.5.0
[2.4.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/2.3.0...2.4.0
[2.3.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/2.2.0...2.3.0
[2.2.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/2.1.1...2.2.0
[2.1.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/2.1.0...2.1.1
[2.1.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.8...2.0.0
[1.5.8]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.7...1.5.8
[1.5.7]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.6...1.5.7
[1.5.6]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.5...1.5.6
[1.5.5]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.4...1.5.5
[1.5.4]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.3...1.5.4
[1.5.3]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.2...1.5.3
[1.5.2]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.4.1...1.5.0
[1.4.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.3.1...1.4.0
[1.3.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/1.0.0...1.1.0
[.travis.dist.yml]: https://github.com/moodlehq/moodle-plugin-ci/blob/master/.travis.dist.yml
