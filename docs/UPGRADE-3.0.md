---
layout: page
title: Upgrading from 2.x to 3.0
---

This document outlines the steps one should take when upgrading to the new major version.

## Step 1: Review the new requirements

Requirements have changed, this project now requires PHP 7.0 or later and Moodle 3.2 or later.

## Step 2: Review the change log

Detailed information about what changed in Version 3 can be found in the [change log](CHANGELOG.md).

## Step 3: Review the Travis CI configuration file

Review the updated [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/.travis.dist.yml)
and update your `.travis.yml` file in your plugin. For detailed information about the contents of `.travis.dist.yml`
file, please see this [help document](TravisFileExplained.md).  **Please carefully** review the updated
`.travis.dist.yml` as some steps have been removed (like installation of Java 8, installing of NodeJS, etc.) and others added.

A summary of the actions required may be:

1. Move to `moodlehq/moodle-plugin-ci` and bump to version 3 (^3).
2. Ensure that the `docker` service is available.
3. Remove any use of selenium or browsers, now they are handled automatically  using docker images.
4. Remove any use of manual `nvm`, `node` or `npm` stuff (unless it's to install components specific for your plugin). Now that's handled automatically too.

Note: Still, it's possible to configure the `node` version to be used, in case you want to experiment or have any exact need for a given plugin or branch. This is how it works, in order of precedence, fall-backing to next:

1. `--node-version` (install option).
2. `NODE_VERSION` (env variable).
3. `.nvmrc` (file present since Moodle 3.5).
4. `lts/carbon` (last resort, default/legacy before Moodle 3.5).

## FAQ

### What is happening to Version 2?

Version 2 still exists as its last release [2.5.0](https://github.com/moodlehq/moodle-plugin-ci/tree/2.5.0).  You can continue
to use it, but it is no longer getting new features and may not receive additional updates.  In addition, it may start breaking in Moodle 3.10 or later.

### Why Version 3?

Mainly to clearly separate development from the original [moodle-plugin-ci](https://github.com/blackboard-open-source/moodle-plugin-ci) as explained [here](https://github.com/moodlehq/moodle-plugin-ci#history-acknowledgement-and-appreciation). Also because, since **Moodle 3.5** we have a better way to control different node/npm versions using [nvm](https://github.com/nvm-sh/nvm) automatically. Finally because we wanted to leave PHP 5.x behind once and forever and this was a good moment to force the jump to **PHP 7**.

### Can I run Version 1 and 3?

If your plugin uses a single Git branch, but still works on Moodle versions older than 3.2, then you might be wanting
to try to use both versions in a single `.travis.yml` file.  First, this is not recommended due to its complexity.
The preferred method is to use separate branches so your `.travis.yml` file is only using one version or another.
If that is not desirable, then one could go about it by using environment variables.  First, be sure that you update
your `.travis.yml` file as explained above.  Then a rough and **untested** example might be:

```yaml
# WARNING - this is only a partial example, several steps were excluded to keep it simple!

env:
  # Define a V3 flag to conditionally run commands.
  - MOODLE_BRANCH=MOODLE_31_STABLE DB=pgsql V3=false
  - MOODLE_BRANCH=MOODLE_32_STABLE DB=mysqli V3=true

before_install:
  # You must install the correct version of this project:
  - if [ "$V3" = false ]; then composer create-project -n --no-dev --prefer-dist moodlehq/moodle-plugin-ci ci ^1; fi
  - if [ "$V3" = true ]; then composer create-project -n --no-dev --prefer-dist moodlehq/moodle-plugin-ci ci ^3; fi

script:
  # Example of a Version 3 execution:
  - if [ "$V3" = true ]; then ...; fi

  # Example of a Version 1 execution:
  - if [ "$V3" = false ]; then ...; fi

  # And of course some exist in both versions, so just call it normally:
  - moodle-plugin-ci phpunit
```
