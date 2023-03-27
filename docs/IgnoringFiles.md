---
layout: page
title: Ignoring files
---

For some of the code analysis tools, it is important to ignore some files within the plugin because they might not be
fixable, like a third party library.  The all code analysis commands in this project ignore files and directories
listed in the [thirdpartylibs.xml](https://docs.moodle.org/dev/Plugin_files#thirdpartylibs.xml) plugin file.

Specifically for the `codechecker` command, you can ignore a single line, a section of a file or the whole file by
using specific PHP comments.  For details see this
[PHP_CodeSniffer wiki page](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Advanced-Usage).

In addition, you can ignore additional files by defining `IGNORE_PATHS` and/or `IGNORE_NAMES` environment variables
in your CI workflow file.  These environment variables wont work for Grunt tasks, but will for everything else.

`.github/workflow/*` example:

```yaml
      - name: Install moodle-plugin-ci
        run: |
          moodle-plugin-ci install --plugin ./plugin --db-host=127.0.0.1
        env:
          DB: ${{ matrix.database }}
          MOODLE_BRANCH: ${{ matrix.moodle-branch }}
          IGNORE_PATHS: 'vendor/widget,javascript/min-lib.js'
          IGNORE_NAMES: '*-m.js,bad_lib.php'
```

`.travis.yml` example:

```yaml
env:
 global:
  - MOODLE_BRANCH=MOODLE_32_STABLE
  - IGNORE_PATHS=vendor/widget,javascript/min-lib.js
  - IGNORE_NAMES=*-m.js,bad_lib.php
 matrix:
  - DB=pgsql
  - DB=mysqli
```

Both environment variables take a CSV value.  For `IGNORE_PATHS`, it takes relative file paths to ignore.  File paths
can be a simple string like `foo/bar` or a regular expression like `/^foo\/bar/`.  For `IGNORE_NAMES`, it takes
file names to ignore.  File names can be a simple string like `foo.php`, a glob like `*.php` or a regular expression
like `/\.php$/`.

If you need to specify ignore paths for a specific command, then you can define additional environment variables.  The
variable names are the same as above, but prefixed with `COMMANDNAME_`.

`.github/workflow/*` example:

```yaml
      - name: Install moodle-plugin-ci
        run: |
          moodle-plugin-ci install --plugin ./plugin --db-host=127.0.0.1
        env:
          DB: ${{ matrix.database }}
          MOODLE_BRANCH: ${{ matrix.moodle-branch }}
          CODECHECKER_IGNORE_PATHS: 'vendor/widget,javascript/min-lib.js'
          CODECHECKER_IGNORE_NAMES: '*-m.js,bad_lib.php'
          MUSTACHE_IGNORE_NAMES: 'broken.mustache'
```

`.travis.yml` example:

```yaml
env:
 global:
  - MOODLE_BRANCH=MOODLE_32_STABLE
  - CODECHECKER_IGNORE_PATHS=vendor/widget,javascript/min-lib.js
  - CODECHECKER_IGNORE_NAMES=*-m.js,bad_lib.php
  - PHPUNIT_IGNORE_PATHS=$IGNORE_PATHS,cli
 matrix:
  - DB=pgsql
  - DB=mysqli
```

In the above example, we are adding the `cli` path to our ignore paths for the PHPUnit command (this is also how you
can ignore files for code coverage).  Please note that this is a complete override and there is no merging with
`IGNORE_PATHS` and `IGNORE_NAMES`.  So, in the above, the PHPUnit command would not ignore the file names
defined in `IGNORE_NAMES`.
