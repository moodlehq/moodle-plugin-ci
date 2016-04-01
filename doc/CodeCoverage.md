# Generating code coverage

Currently, code coverage is only generated for builds that are running on PHP7 or later.  Code coverage generation
is significantly faster and easier to produce in PHP7.

The way you generate code coverage is to use one of the coverage options on the `phpunit` command.  The currently
available options are `--coverage-text` and `--coverage-clover`.  The easiest way to start generating code coverage
is to use the text option as that gets printed in the Travis CI logs.  Example:

```yaml
script:
  - moodle-plugin-ci phpunit --coverage-text
```

You can use the `--coverage-clover` option to generate a `coverage.xml` in the current working directory.  Example:

```yaml
script:
  - moodle-plugin-ci phpunit --coverage-clover

  # Then you can view it or do whatever you like with it.
  - cat coverage.xml
```

You can of course use both options at the same time if you like.

# Coveralls integration

Text based coverage and a coverage XML file are fine, but how about a detailed report that tracks coverage over time?
To do exactly that, this project supports an integration with [Coveralls](https://coveralls.io), though you could
technically take the `coverage.xml` file and upload it to any service you like.

If you would like to use Coveralls, then go to https://coveralls.io and login with your GitHub account.  You will
need to authorize access to your public repositories.  Once you have done that, navigate to your
[REPOS](https://coveralls.io/repos) listing and use the _ADD REPOS_ button in the upper right to turn on your project.

Then, you need to make the following changes to your plugin's `.travis.yml` file.  You need to tell the `phpunit`
command to generate clover coverage and then use the `coveralls-upload` command to actually upload the coverage.
Example:

```yaml
script:
  # ...snip
  - moodle-plugin-ci phpunit --coverage-clover
  # ...snip

after_success:
  - moodle-plugin-ci coveralls-upload
```

Now commit those changes to your plugin and push the results up to GitHub.  After Travis CI runs, then Coveralls
will be updated with a new coverage report.

**Tips on troubleshooting:** if you are having problems with sending coverage to Coveralls, do not forget that you
can expand the `coveralls-upload` line in the Travis CI logs where there might be some details as to why.  Also don't
forget that coverage is only generated for PHP7 or later and this applies to the Coveralls integration as well.