---
layout: page
title: Upgrading from 4.x to 5.0
---

This document outlines the steps one should take when upgrading to the new major version.

## Step 1: Review the new requirements

Requirements have changed, this project now requires **PHP 8.1 or later** and **Moodle 4.1** or later.

## Step 2: Review the change log

Detailed information about what changed in Version 5 can be found in the [change log](CHANGELOG.md).

The following features, deprecated during the 4.x series, have been removed:

* **Travis CI support**: the `.travis.dist.yml` template and its documentation are gone. Please migrate to
  [GitHub Actions](GHAFileExplained.md) or any other CI service.
* **`TRAVIS_BUILD_DIR` environment variable**: no longer used to detect the plugin directory. Use the
  `--plugin` option of the `install` command or the tool-agnostic `CI_BUILD_DIR` environment variable instead.
* **`phpdbg` code coverage**: both the `--coverage-phpdbg` option and the automatic fallback to `phpdbg` have been
  removed. Make sure that either `pcov` or `xdebug` is available when generating code coverage.
* **`phpcpd` command**: the PHP Copy/Paste Detector command has been removed. No replacement is planned, simply remove
  it from your workflows.

## Step 3: Review the CI configuration file

Review the updated [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/gha.dist.yml)
and update the GitHub Action workflow file in your plugin (for example, `.github/workflows/ci.yml`). For detailed information about the contents of that `ci.yml` file, please see our [sample GitHub Action workflow explanation](GHAFileExplained.md).

A summary of the actions required for version 5 is:

1. Change the `composer create-project` line and bump from version 4 (`^4`) to version 5 (`^5`).
2. Remove any use of the `phpcpd` command and the `--coverage-phpdbg` option.
3. If generating code coverage, set `coverage` in the `shivammathur/setup-php` step to `pcov` or `xdebug`.
4. Test it and, hopefully, party!

## FAQ

### What is happening to Version 4?

Version 4 still exists within its own [4.x](https://github.com/moodlehq/moodle-plugin-ci/tree/4.x) branch. You can continue using it, but it is no longer getting new features and may not receive additional updates.

### Why Version 5?

Mainly to upgrade tooling, remove features that were deprecated during the 4.x series (Travis CI, `phpdbg` coverage and `phpcpd`) and to leave behind old versions of PHP (< 8.1) and Moodle (< 4.1), which have not been supported for a long time.

### Can I run Version 4 and 5?

If your plugin uses a single Git branch, but still works on Moodle versions older than 4.1, then you might want to try using both versions in a single GitHub Action workflow.

First of all, this is not recommended due to its complexity. The preferred method is to use separate branches so your CI files are only using one version or another.

Despite our recommendations, if you still want to run both versions in the same CI process, you can set environment variables to specify which version to use for every job.

For example, for GitHub Actions, it can be setup as follows:

<!-- {% raw %} -->
```yaml
# WARNING - this is only a partial example,
# several steps were excluded to keep it simple!
    strategy:
      fail-fast: false
      matrix:
        include:
          - php: '8.4'
            moodle-branch: 'MOODLE_503_STABLE'
            database: pgsql
            plugin-ci: ^5 # Decide the version to use per matrix element.
          ...
          ...
          - php: '8.0'
            moodle-branch: 'MOODLE_400_STABLE'
            database: mariadb
            plugin-ci: ^4 # Decide the version to use per matrix element.

    ...
    ...
    steps:
      - name: Check out repository code
        uses: actions/checkout@v6
        with:
          path: plugin
      ...
      ...
      - name: Initialise moodle-plugin-ci
        run: |
          # Apply here for the configured plugin-ci version.
          composer create-project -n --no-dev --prefer-dist moodlehq/moodle-plugin-ci ci ${{ matrix.plugin-ci }}
          echo $(cd ci/bin; pwd) >> $GITHUB_PATH
          echo $(cd ci/vendor/bin; pwd) >> $GITHUB_PATH
          ...
          ...
```
<!-- {% endraw %} -->
