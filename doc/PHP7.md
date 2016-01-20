# Testing a plugin against PHP7

Travis CI makes it very easy to test your plugin in multiple versions of PHP and PHP7 is no different.  The only trick
to testing your plugin in PHP7 is that you must ensure that you are testing against Moodle 3 stable or later as
support for PHP7 was added in Moodle 3.0.1.

The recommended `.travis.dist.yml` demonstrates how to run your plugin across all the supported PHP versions while using
a Moodle build that supports PHP7.  Sometimes though, you may have more complex `.travis.yml` where you are testing a
single version of your plugin across multiple versions of Moodle.  If you use a Moodle version older than Moodle 3
and you have PHP7 as one of your PHP versions, then you will undoubtedly run into problems.  By using a build matrix,
we can ensure PHP7 is only used for specific Moodle versions.

Example of using a build matrix:

```yaml
php:
 - 5.4

env:
  - MOODLE_BRANCH=MOODLE_29_STABLE DB=pgsql
  - MOODLE_BRANCH=MOODLE_29_STABLE DB=mysqli

matrix:
  include:
    - php: 7.0
      env: MOODLE_BRANCH=MOODLE_30_STABLE DB=pgsql
    - php: 7.0
      env: MOODLE_BRANCH=MOODLE_30_STABLE DB=mysqli
```

The above will generate the following builds:
* Moodle 2.9 stable using Postgres and PHP5.4.
* Moodle 2.9 stable using MySQL and PHP5.4.
* Moodle 3.0 stable using Postgres and PHP7.
* Moodle 3.0 stable using MySQL and PHP7.

The matrix also supports a way of excluding specific build combinations.  Customize it to your liking.  For further
reading, please see [Customizing the Build](https://docs.travis-ci.com/user/customizing-the-build/) documentation,
specifically the _Build Matrix_ section.