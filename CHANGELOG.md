# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

The format of this change log follows the advice given at [Keep a CHANGELOG](http://keepachangelog.com).

## [Unreleased]
No unreleased changes.

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

[Unreleased]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.5.5...master
[1.5.4]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.5.4...1.5.5
[1.5.4]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.5.3...1.5.4
[1.5.3]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.5.2...1.5.3
[1.5.2]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.5.1...1.5.2
[1.5.1]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.5.0...1.5.1
[1.5.0]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.4.1...1.5.0
[1.4.1]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.4.0...1.4.1
[1.4.0]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.3.1...1.4.0
[1.3.1]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.2.0...1.3.0
[1.2.0]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.0.0...1.1.0