---
layout: page
title: Upgrading from 1.X to 2.0
---

This document outlines the steps one should take when upgrading to the new major version.

## Step 1: Review the new requirements

Requirements have changed, this project now requires PHP 5.6 or later and Moodle 3.2 or later.

## Step 2: Review the change log

Detailed information about what changed in Version 2 can be found in the [change log](CHANGELOG.md).

## Step 3: Review the Travis CI configuration file 

Review the updated [.travis.dist.yml](https://github.com/moodlerooms/moodle-plugin-ci/blob/master/.travis.dist.yml)
and update your `.travis.yml` file in your plugin. For detailed information about the contents of `.travis.dist.yml`
file, please see this [help document](TravisFileExplained.md).  **Please carefully** review the updated
`.travis.dist.yml` as some steps have been removed and others added, like installation of Java 8,
upgrade of NodeJS, etc.

## FAQ

### What is happening to Version 1?

Version 1 still exists in the [v1](https://github.com/moodlerooms/moodle-plugin-ci/tree/v1) branch.  You can continue
to use it, but it is no longer getting new features and may not receive additional updates.  In addition, it may start
breaking in Moodle 3.2 or later.

### Why Version 2?

Due to changes in developer tools in Moodle 3.2.  Moodle is using Grunt now for linting and building assets.
To keep efficient build times and a maintainable tool, this project decided to drop support for the old tools
and add support for the new tools all in one step.

### Can I run Version 1 and 2?

If your plugin uses a single Git branch, but still works on Moodle versions older than 3.2, then you might be wanting
to try to use both versions in a single `.travis.yml` file.  First, this is not recommended due to its complexity.
The preferred method is to use separate branches so your `.travis.yml` file is only using one version or another.
If that is not desirable, then one could go about it by using environment variables.  First, be sure that you update
your `.travis.yml` file as explained above.  Then a rough and **untested** example might be:

```yaml
# WARNING - this is only a partial example, several steps were excluded to keep it simple! 

env:
  # Define a V2 flag to conditionally run commands.
  - MOODLE_BRANCH=MOODLE_31_STABLE DB=pgsql V2=false
  - MOODLE_BRANCH=MOODLE_32_STABLE DB=mysqli V2=true

before_install:
  # You must install the correct version of this project:
  - if [ "$V2" = false ]; then composer create-project -n --no-dev --prefer-dist moodlerooms/moodle-plugin-ci ci ^1; fi
  - if [ "$V2" = true ]; then composer create-project -n --no-dev --prefer-dist moodlerooms/moodle-plugin-ci ci ^2; fi

script:
  # Example of a Version 2 only command:
  - if [ "$V2" = true ]; then moodle-plugin-ci mustache; fi
  
  # Example of a Version 1 only command:
  - if [ "$V2" = false ]; then moodle-plugin-ci csslint; fi

  # And of course some exist in both versions, so just call it normally:  
  - moodle-plugin-ci phpunit
```
