# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

The format of this change log follows the advice given at [Keep a CHANGELOG](http://keepachangelog.com).

## [Unreleased]

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

[Unreleased]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.2.0...master
[1.2.0]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/moodlerooms/moodle-plugin-ci/compare/1.0.0...1.1.0