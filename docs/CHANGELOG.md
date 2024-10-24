---
layout: page
title: Change log
---

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

The format of this change log follows the advice given at [Keep a CHANGELOG](https://keepachangelog.com).

## [Unreleased]
### Added
- Improvments to plugin validation implementation:
    `getRequiredFunctionCalls` in plugin type specific `Requirements` class can be used to validate that file contains function call.
    `FileTokens::notFoundHint` can be used to give some context for validation error to improve DX.

### Fixed
- Fixed stylelinting error in non-theme plugins containing scss.
- Updated filter plugin validation requirements to comply with Moodle 4.5

### Removed
- Stylelint less component task (`grunt stylelint:less`) has been deprecated in
  Moodle 3.7.

## [4.5.4] - 2024-08-23
### Changed
- Fixed nvm loading issue caused by upstream regression.

## [4.5.3] - 2024-07-05
### Added
- Support for version 4.4 of the app, that uses new defaults and Chrome (Selenium 4) version.

### Changed
- Updated project dependencies to current [moodle-cs v3.4.10](https://github.com/moodlehq/moodle-cs) and [moodle-local_ci v1.0.31](https://github.com/moodlehq/moodle-local_ci) releases.

## [4.5.2] - 2024-06-19
### Changed
- Updated project dependencies to current [moodle-cs v3.4.9](https://github.com/moodlehq/moodle-cs) release.

## [4.5.1] - 2024-06-14
### Changed
- Updated project dependencies to current [moodle-cs v3.4.8](https://github.com/moodlehq/moodle-cs) release.

### Fixed
- Fixed a problem with the `grunt` command running the `stylelint` tasks against the whole Moodle directory (including both core and other optional plugins installed). Now only the plugin being checked is effectively analysed.

## [4.5.0] - 2024-06-03
### Changed
- Updated project dependencies to current [moodle-cs v3.4.7](https://github.com/moodlehq/moodle-cs) and [moodle-local_ci v1.0.30](https://github.com/moodlehq/moodle-local_ci) releases.
- Internal, various improvements to self testing.

### Deprecated
- The use of `phpdbg` to calculate PHPUnit's code-coverage has been deprecated in this `moodle-plugin-ci` release (4.5.0) and will be removed in 5.0.0. This includes both the implicit (default) option when no alternative (`pcov` or `xdebug`) is available and the explicit `--coverage-phpdbg` option.
- ACTION SUGGESTED: In order to avoid deprecation warnings or annotations, proceed to ensure that either `pcov` (Moodle 3.10 and up) or `xdebug` are available and they will be used automatically. Note that any use of `phpdbg` will throw an error in the next major release (5.0.0).

### Fixed
- Solved a problem with the validation of `dataformat` plugin lang strings.
- Fixed a problem with the `phpcs` command returning with success when some (configuration, installation, ...) problem was causing it not to be executed at all.

## [4.4.5] - 2024-04-03
### Changed
- Additional release for error in release process

## [4.4.4] - 2024-04-03
### Changed
- Updated project dependencies to current [moodle-cs v3.4.6](https://github.com/moodlehq/moodle-cs) release.

## [4.4.3] - 2024-03-31
### Changed
- Updated project dependencies to current [moodle-cs v3.4.5](https://github.com/moodlehq/moodle-cs) release.

## [4.4.2] - 2024-03-30
### Added
- Added GHA step to store Behat fail-dumps as workflow artefacts, so it can be
  [inspected](https://docs.github.com/en/actions/managing-workflow-runs/downloading-workflow-artifacts). Documentation has been updated as well to reflect the purpose of the step.
- Added support for the `--license-regex` option to the `phpcs` command. When specified, all the PHPDoc license tags (`@license`) are inspected to ensure that they contain some text matching the regular expression (a license type: `/GNU GPL v3 or later/`, ... or any other valid alternative).

### Changed
- Updated project dependencies to current [moodle-cs v3.4.4](https://github.com/moodlehq/moodle-cs), [moodle-local_moodlecheck v1.3.2](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci v1.0.29](https://github.com/moodlehq/moodle-local_ci) releases.
- Updated self CI dependencies to current PHP and Moodle versions.
- For pull requests, set the Assignee automatically (to the author of the PR).

### Removed
- The documentation about the `phpcpd` command (deprecated and to be removed in 5.0.0) has been deleted from all the templates and docs.

## [4.4.1] - 2024-03-08
### Added
- New `--no-plugin-node` option added to the `install` command, to be able to skip the installation of any NodeJS stuff that the plugin may include. The previous default has not changed and both Moodle's and plugin's NodeJS installation continues happening normally.

### Changed
- Updated project dependencies to current [moodle-cs v3.4.1](https://github.com/moodlehq/moodle-cs), [moodle-local_moodlecheck v1.3.0](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci v1.0.28](https://github.com/moodlehq/moodle-local_ci) releases.

### Fixed
- Some small fixes to documentation.

## [4.4.0] - 2024-02-16
### Added
- New `--selenium` option or `MOODLE_BEHAT_SELENIUM_IMAGE` env variable to the `behat` command, to be able to specify the Selenium Docker image to use (defaults apply if not specified).
- New `MOODLE_BEHAT_CHROME_CAPABILITIES` and `MOODLE_BEHAT_FIREFOX_CAPABILITIES` env variables to configure additional browser capabilities (they will be needed - internally - soon, to allow the command to perform some special behat runs).
- Extend own CI tests to cover:
    - PHP 8.3 (all tests).
    - `selfupdate` PHAR command (unit and integration tests).

### Changed
- Updated all uses of `actions/checkout` from `v3` (using node 16) to `v4` (using node 20), because [actions using node 16 are deprecated](https://github.blog/changelog/2023-09-22-github-actions-transitioning-from-node-16-to-node-20/) and will stop working in the future.
- ACTION SUGGESTED: In order to avoid the node 16 deprecation warnings, update your workflows to use `actions/checkout@v4`. Note: the same may apply to other actions being used in your workflows (check your latest runs).
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs).

### Deprecated
- The `phpcpd` command (that uses the [PHP Copy/Paste Detector](https://github.com/sebastianbergmann/phpcpd), now abandoned) has been deprecated in this `moodle-plugin-ci` release (4.4.0) and will be removed in 5.0.0. No replacement is planned.
- ACTION SUGGESTED: In order to avoid deprecation warnings or annotations, proceed to remove this command from your workflows. Note that any use will throw an error in the next major release (5.0.0).
- The `master` branch of Moodle upstream repositories has been moved to `main` and will stop working soon (see [MDLSITE-7418](https://tracker.moodle.org/browse/MDLSITE-7418) for details). GitHub workflows will start emitting warnings/annotations when uses of the `master` branch are detected.
- ACTION SUGGESTED: In order to avoid deprecation warnings or annotations, proceed to replace `master` by `main` in your workflows. Note that any use of the former (to be removed) will throw an error in the future.

## [4.3.2] - 2024-01-26
### Changed
- Modified internal CI scripts towards better Codecov future support.
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs).

## [4.3.1] - 2024-01-19
### Added
- Added support for the `--todo-comment-regex` option to the `phpcs` command. When specified, all the todo comments (`TODO` and `@todo`) are inspected to ensure that they contain some text matching the regular expression (a tracker issue key: `MDL-[0-9]+`, a link to GitHub: `github.com/moodle/moodle/pull/[0-9]+`, ... or any other valid alternative).

### Changed
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

## [4.3.0] - 2023-12-19
### Added
- Added [Moodle App](MoodleApp.md) Behat testing support.
- Added support for the `--exclude` option to the `phpcs` command.

### Changed
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

## [4.2.0] - 2023-11-30
### Added
- Added support for the `--tags` and `--name` options to the `behat` command.
- Added support for the `--configure`, `--testsuite` and `--filter` options to the `phpunit` command.

### Changed
- The default branch of this repository has been renamed from `master` to `main`. You can visit [this issue (#258)](https://github.com/moodlehq/moodle-plugin-ci/issues/258) for more information about the potential actions required (if you use this, or clones/forks of this, repository).
- ACTION SUGGESTED: If you are using GitHub Actions, it's recomended to use `!cancelled()` instead of `always()` for moodle-plugin-ci tests. Adding a final step that always returns failure when the workflow is cancelled will ensure that cancelled workflows are not marked as successful. For a working example, please reference the updated `gha.dist.yml` file.
- ACTION SUGGESTED: For some (unknown) reason, Travis environments with PHP 8.2 have started to fail with error:

  ```
  php: error while loading shared libraries: libonig.so.5
  ```

  To avoid that problem, it's recommended to to add the `libonig5` package to the `travis.yml` file. For a working example, please reference the updated `.travis.dist.yml`file.
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs) and [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) versions.

## [4.1.8] - 2023-10-20
### Changed
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs) version.

### Added
- Added back the `selfupdate` command, that enables easy updates of the PHAR package. Note this is experimental and may show some warnings with PHP 8.x.

## [4.1.7] - 2023-10-11
### Changed
- Updated project dependencies to current [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) version.

### Added
- Added some docs regarding the `moodle-plugin-ci` use [in local dev environments](https://moodlehq.github.io/moodle-plugin-ci/#using-in-a-local-dev-environment).

### Fixed
- Fixed the `phpcmd` command for compatibility with versions `^2.14.0`.

## [4.1.6] - 2023-09-28
### Changed
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

### Fixed
- Updated the `.gitattributes` file towards better packaging and distribution.

## [4.1.5] - 2023-09-22
### Changed
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs) version.

## [4.1.4] - 2023-09-22
### Added
- Covered the PHAR package with some new integration tests.

### Changed
- Reduced the number of own CI tests (internal change) executed with Travis.
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

### Fixed
- Solved various problems related with the execution of checks from PHAR:
  - Fixed the `.env` support.
  - Fixed the `mustache` command execution.
  - Fixed the `phpcs` and `phpcbf` commands execution.
  - Fixed the `phpdoc` command to use the bundled `coreapis.txt` file.

## [4.1.3] - 2023-09-08
### Changed
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs), [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions. Also, to various internal / development tools.

### Fixed
- Updated the PHAR packaging utility ([box](https://github.com/box-project/box)) to actual one, to avoid various issues happening with PHP 8.1 and up.

## [4.1.2] - 2023-09-02
### Changed
- Modified `moodle-local_ci` composer dependencies and manage them normally, removing some ancient bits that have [stopped working with Composer 2.6.0 and up](https://github.com/composer/composer/issues/11613).
- Updated project dependencies to current [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) version.

## [4.1.1] - 2023-07-14
### Added
- Add support for the following optional env variables, that will be used on installation, getting precedence over the corresponding existing option:
    - `DB_USER`: To specify the database username (alternative to `--db-user`)
    - `DB_PASS`: To specify the database password (alternative to `--db-pass`)
    - `DB_NAME`: To specify the database name (alternative to `--db-name`)
    - `DB_HOST`: To specify the database host (alternative to `--db-host`)
    - `DB_PORT`: To specify the database port (alternative to `--db-port`)

    Note that these new env variables behave exactly the same than the existing (and often used) `DB` one, that is also a priority alternative to `--db-type` on install.

### Changed
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

## [4.1.0] - 2023-05-29
### Added
- Add the `--testdox` option to the `phpunit` command.
- Add the `--max-warnings` option to the `phpdoc` command, so it behaves the same than the `phpcs` command. Note that this modifies current behaviour (see next point) and plugins with only warnings won't be failing any more.
- ACTION SUGGESTED: In order to keep the previous behaviour, it's recommended to use the new `--max-warnings 0` (or any other number) option to specify the warnings threshold.

### Changed
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs), [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions. Also, to various internal / development tools.


### Fixed
- Fix problems in the documentation causing coding snippets to display partially.

## [4.0.0] - 2023-05-03
### Added
- Upgrade guide: [Upgrading from 3.x to 4.0](UPGRADE-4.0.md)
- Support for PHP 8.1 and up.

### Changed
- Lots of internals upgraded:
  - [Symfony 5.4](https://symfony.com/releases/5.4).
  - [PHPUnit 9.x](https://phpunit.de/announcements/phpunit-9.html).
  - [Code coverage](https://app.codecov.io/gh/moodlehq/moodle-plugin-ci) reporting.
  - [Psalm level 2](https://psalm.dev/docs/running_psalm/error_levels/) compliance.
  - [PSR-12](https://www.php-fig.org/psr/psr-12/) compliance.
- Small changes to documentation towards prioritise GitHub Actions over Travis CI.
- In addition to the internal and doc changes above, this initial 4.x release has 100% feature-parity with current 3.x series, setting the base for further improvements and new features. No changes are expected in general, other than in order to meet the new requirements.

### Removed
- Support for PHP < 7.4 (the new minimum version).
- Support for Moodle < 3.8.3, that was the [first release officially supporting PHP 7.4](https://moodledev.io/general/development/policies/php#php-74).

## [3.4.12] - 2023-05-03
### Changed
- Small changes to documentation towards prioritise GitHub Actions over Travis CI.

## [3.4.11] - 2023-03-22
### Changed
- Plugin bundled `phpunit.xml` files are not overwritten or modified ever.
- For Moodle 3.9 and up, when the plugin is missing any `tests/coverage.php` file, core defaults (`lib.php`, `locallib.php`, `classes/`, ...) will be applied. Previously, all the `*.php` files were applied by default (note that older Moodle versions will continue getting them).

## [3.4.10] - 2023-03-14
### Changed
- Various improvements to the Travis & GHA dist files and documentation.
- Updated project dependencies to current [moodle-cs](https://github.com/moodlehq/moodle-cs) version.

## [3.4.9] - 2023-03-08
### Added
- Improved release process: Now every version changelog (this file) is automatically added to the release notes.
- New `--test-version` option added to the `phpcs` (`codechecker`) command in order to specify the PHP version `(x.y)` or range of PHP versions `(x.y-w.z)` to be checked by the `PHPCompatibility` standard (part of the `moodle` standard).

## [3.4.8] - 2023-03-06
### Changed
- Modified the Travis templates and docs to point that, since [MDL-75012](https://tracker.moodle.org/browse/MDL-75012) (core update to Node 18), Ubuntu Focal 20.04 is the minimum required by runs.
- ACTION REQUIRED: Review any Travis configuration for 39_STABLE and up. Now they require Ubuntu 20.04 (focal) to be specified.
- The `codechecker` command has been renamed to `phpcs`, to better match other command names. The old name remains as alias, so no change is required.

## [3.4.7] - 2023-03-04
### Changed
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) version.
- Fix self-tests to pass with new introduced checks and Node 18.

## [3.4.6] - 2023-02-08
### Changed
- Updated project dependencies to current [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) version.

## [3.4.5] - 2023-01-23
### Changed
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

### Fixed
- Updated to `php-coveralls/php-coveralls` v2 for uploading coverage results to [Coveralls](https://coveralls.io) with the `coveralls-upload` command.
- ACTION REQUIRED: Review any use of the `coveralls-upload` command in GHA and ensure that `COVERALLS_REPO_TOKEN` is set in the environment. See [Coveralls integration](https://github.com/moodlehq/moodle-plugin-ci/blob/main/docs/CodeCoverage.md#coveralls-integration) for more information.

## [3.4.4] - 2023-01-20
### Changed
- Updated to `php-compatibility` dev version. This was needed because the last release is from 2019 and, until a new release is available, it was the only way to get it working with PHP 8.1 and above and some good new Sniffs incorporated.

## [3.4.3] - 2022-12-24
### Changed
- Updated [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/gha.dist.yml) and
  [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/.travis.dist.yml)
  (and documentation) to fulfil [Moodle 4.2 new requirements](https://tracker.moodle.org/browse/MDL-74905).
- ACTION REQUIRED: Review existing integrations running tests against main (4.2dev). There are a few Moodle 4.2 new requirements:
  - PHP 8.0 is required (instead of 7.4).
  - PostgreSQL 13 is required (instead of 12).
  - MariaDB 10.6 is required (instead of 10.4).
  - MySQL 8 is required (instead of 5.7).

## [3.4.2] - 2022-10-18
### Changed
- Internal improvements to the release process.
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.
- Updated all uses of `actions/checkout` from `v2` (using node 12) to `v3` (using node 16), because [actions using node 12 are deprecated](https://github.blog/changelog/2022-09-22-github-actions-all-actions-will-begin-running-on-node16-instead-of-node12/) and will stop working in the future.
* ACTION SUGGESTED: In order to avoid the node 12 deprecation warnings, update your workflows to use `actions/checkout@v3`.

## [3.4.1] - 2022-09-21
### Changed
- Updated all GHA scripts, templates and guides to use Ubuntu 22.04 (jammy). This change is not required yet, but integrations using old versions (Ubuntu 16.04, 18.04...) may face problems in the future because they are being deprecated.
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

## [3.4.0] - 2022-07-27
### Added
- `moodle-plugin-ci install` now provides an option `--db-port` to define a custom database port.

### Changed
- Updated [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/gha.dist.yml) and
  [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/.travis.dist.yml)
  (and documentation) to fulfil [Moodle 4.1 new requirements](https://tracker.moodle.org/browse/MDL-71747).
- ACTION REQUIRED: Review existing integrations running tests against main (4.1dev). There are a few Moodle 4.1 new requirements:
  - PHP 7.4 is required (instead of 7.3).
  - PostgreSQL 12 is required (instead of 10). Pay special attention to the changes needed for this and Travis!
  - MariaDB 10.4 is required (instead of 10.2.29).
  - Oracle 19 is required (instead of 11.2).
- Updated project dependencies to current [moodle-local_moodlecheck](https://github.com/moodlehq/moodle-local_moodlecheck) and [moodle-local_ci](https://github.com/moodlehq/moodle-local_ci) versions.

## [3.3.0] - 2022-06-28
### Added
- PHPUnit code coverage will now automatically fallback between `pcov` => `xdebug` => `phpdbg`, using the "best" one available in the system. Still, if needed to, any of them can be forced, given all their requirements are fulfilled, using the following new options of the 'phpunit' command: `--coverage-pcov`, `--coverage-xdebug` or `--coverage-phpdbg`.
- ACTION SUGGESTED: Ensure that the `pcov` or `xdebug` extensions are installed in your environment to get 'moodle-plugin-ci' using them automatically.

### Changed
- Switched from [local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to [moodle-cs](https://github.com/moodlehq/moodle-cs) for checking the coding style. Previously, `moodle-plugin-ci` (and other tools) required `local_codechecker` (that includes both `PHP_Codesniffer` and the `moodle` standard) to verify the coding style. Now, the `moodle` standard has been moved to be a standalone repository and all the tools will be using it and installing `PHP_Codesniffer` via composer. No changes in behavior are expected.

### Fixed
- The `--version` option now works both with the `bin/moodle-plugin-ci` binary and the `moodle-plugin-ci.phar` package.

## [3.2.6] - 2022-05-10
### Added
- It is possible to specify more test execution options to the 'phpunit' command, such as `--fail-on-incomplete`, `--fail-on-risky` and `--fail-on-skipped` and `--fail-on-warning`. For more information, see [PHPUnit documentation](https://phpunit.readthedocs.io).

### Fixed
- Locally bundled [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) now works ok with recent (Moodle 3.11 and up) branches. A recent change in those versions was causing [some problems](https://tracker.moodle.org/browse/MDL-74704).

## [3.2.5] - 2022-03-31
### Changed
- ACTION SUGGESTED: Now, it's safe to 'unpin' the MariaDB version in all integrations. With MariaDB 10.6.7 and 10.7.3 already released, the existing problems are gone, so it's possible to move away from the older 10.5 version. To achieve that, just look for any use of `image: mariadb:10.5` and change it to `image: mariadb:10`. For more information, see [MDL-72131](https://tracker.moodle.org/browse/MDL-72131).
- Updated version of [moodle-local_codechecker](https://github.com/moodlehq/moodle-local_codechecker) to v3.1.0. For list of changes see [changelog](https://github.com/moodlehq/moodle-local_codechecker/blob/main/CHANGES.md#changes-in-version-310-20220225---fondant-chocolate), you should expect numerous `@covers` annotation warnings in particular.

### Added
- Use utf8mb4 for MySQL and MariaDB setup.
- ACTION SUGGESTED: If you are using GitHub Actions and running tests on MySQL/MariaDB, set env variables `MYSQL_CHARACTER_SET_SERVER` and `MYSQL_COLLATION_SERVER` for mysql/mariadb service per `gha.dist.yml` file.

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
- Updated [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/gha.dist.yml) and
  [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/.travis.dist.yml)
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
  [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/gha.dist.yml) and
  [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/.travis.dist.yml) for more information.

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
  [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/gha.dist.yml)
  and add missing `NVM_DIR` line your plugin's GHA workflow file.

### Changed
- `moodle-plugin-ci add-plugin` command now uses default banch to checkout
  instead of `main` if `--branch` param is not specified..

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
- Add [manual](https://github.com/moodlehq/moodle-plugin-ci/blob/main/docs/index.md) and [example](https://github.com/moodlehq/moodle-plugin-ci/blob/main/docs/GHAFileExplained.md) on using GitHub Actions as CI tool.

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
- Upgrade guide: [Upgrading from 2.x to 3.0](UPGRADE-3.0.md)

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

[Unreleased]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.5.4...main
[4.5.4]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.5.3...4.5.4
[4.5.3]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.5.2...4.5.3
[4.5.2]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.5.1...4.5.2
[4.5.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.5.0...4.5.1
[4.5.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.4.5...4.5.0
[4.4.5]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.4.4...4.4.5
[4.4.4]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.4.3...4.4.4
[4.4.3]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.4.2...4.4.3
[4.4.2]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.4.1...4.4.2
[4.4.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.4.0...4.4.1
[4.4.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.3.2...4.4.0
[4.3.2]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.3.1...4.3.2
[4.3.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.3.0...4.3.1
[4.3.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.2.0...4.3.0
[4.2.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.8...4.2.0
[4.1.8]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.7...4.1.8
[4.1.7]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.6...4.1.7
[4.1.6]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.5...4.1.6
[4.1.5]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.4...4.1.5
[4.1.4]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.3...4.1.4
[4.1.3]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.2...4.1.3
[4.1.2]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.1...4.1.2
[4.1.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.1.0...4.1.1
[4.1.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/4.0.0...4.1.0
[4.0.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.12...4.0.0
[3.4.12]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.11...3.4.12
[3.4.11]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.10...3.4.11
[3.4.10]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.9...3.4.10
[3.4.9]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.8...3.4.9
[3.4.8]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.7...3.4.8
[3.4.7]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.6...3.4.7
[3.4.6]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.5...3.4.6
[3.4.5]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.4...3.4.5
[3.4.4]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.3...3.4.4
[3.4.3]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.2...3.4.3
[3.4.2]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.1...3.4.2
[3.4.1]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.4.0...3.4.1
[3.4.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.3.0...3.4.0
[3.3.0]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.2.6...3.3.0
[3.2.6]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.2.5...3.2.6
[3.2.5]: https://github.com/moodlehq/moodle-plugin-ci/compare/3.2.4...3.2.5
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
[.travis.dist.yml]: https://github.com/moodlehq/moodle-plugin-ci/blob/main/.travis.dist.yml
