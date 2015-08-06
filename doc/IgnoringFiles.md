# Ignoring files

For some of the code analysis tools, it is important to ignore some files within the plugin because they might not be
fixable, like a third party library.  The all code analysis commands in this project ignore files and directories
listed in the [thirdpartylibs.xml](https://docs.moodle.org/dev/Plugin_files#thirdpartylibs.xml) plugin file.

In addition, you can ignore additional files by defining `IGNORE_PATHS` and/or `IGNORE_NAMES` environment variables
in your `.travis.yml` file.  Example:

```yml
env:
 global:
  - MOODLE_BRANCH=MOODLE_29_STABLE
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
