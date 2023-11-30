---
layout: page
title: Upgrading from 3.x to 4.0
---

This document outlines the steps one should take when upgrading to the new major version.

## Step 1: Review the new requirements

Requirements have changed, this project now requires **PHP 7.4 or later** and **Moodle 3.8.3** or later.

## Step 2: Review the change log

Detailed information about what changed in Version 4 can be found in the [change log](CHANGELOG.md).

## Step 3: Review the CIs configuration files

### GitHub Actions

Review the updated [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/gha.dist.yml)
and update the GitHub Action workflow file in your plugin (for example, `.github/workflows/ci.yml`). For detailed information about the contents of that `ci.yml` file, please see our [sample GitHub Action workflow explanation](GHAFileExplained.md).

A summary of the actions required for version 4 is:

1. Change the `composer create-project` line and bump from version 3 (`^3`) to version 4 (`^4`).
2. Test it and, hopefully, party!

### Travis CI

Review the updated [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/.travis.dist.yml)
and update your `.travis.yml` file in your plugin. For detailed information about the contents of `.travis.dist.yml`
file, please see our [sample Travis workflow file explanation](TravisFileExplained.md).

1. Change the `composer create-project` line and bump from version 3 (`^3`) to version 4 (`^4`).
2. Test it and, hopefully, party!

## FAQ

### What is happening to Version 3?

Version 3 still exists within its own [3.x](https://github.com/moodlehq/moodle-plugin-ci/tree/3.x) branch. You can continue using it, but it is no longer getting new features and may not receive additional updates.  In addition, it may start breaking in Moodle 4.3 or later, because of 8.2 incompatibilities.

### Why Version 4?

Mainly to be able to modernise all the internal components of the application which had remained largely unmodified since version 2. This will make it much easier to plan for the future and to leave behind very old versions of PHP (< 7.4) and Moodle (< 3.8.3), which have not been supported for a long time.

### Can I run Version 3 and 4?

If your plugin uses a single Git branch, but still works on Moodle versions older than 3.8.3, then you might want to try using both versions in a single GiHub Action or Travis file.

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
          - php: 8.2
            moodle-branch: main
            database: pgsql
            plugin-ci: ^4 # Decide the version to use per matrix element.
          ...
          ...
          - php: 7.3
            moodle-branch: MOODLE_37_STABLE
            database: mariadb
            plugin-ci: ^3 # Decide the version to use per matrix element.

    ...
    ...
    steps:
      - name: Check out repository code
        uses: actions/checkout@v3
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

The same can be also implemented for Travis CI jobs, adding the `plugin-ci` version as one more element in the jobs matrix `env` element.
