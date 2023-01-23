---
layout: page
title: Generating code coverage
---

Currently, code coverage is only generated for builds that are running on PHP7 or later.  Code coverage generation
is significantly faster and easier to produce in PHP7.

Code coverage will now automatically fallback between `pcov` => `xdebug` => `phpdbg`, using the "best" one available in the system. Still, if needed to, any of them can be forced, given all their requirements are fulfilled, using the following new options of the 'phpunit' command: `--coverage-pcov`, `--coverage-xdebug` or `--coverage-phpdbg`.

The way you generate code coverage is to use one of the coverage options on the `phpunit` command.  The currently
available options are `--coverage-text` and `--coverage-clover`.  The easiest way to start generating code coverage
is to use the text option as that gets printed in the CI logs.  Example:

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

You can of course use both options at the same time if you like.  Sometimes it is nice to exclude files from code
coverage stats, for example, CLI scripts that would never be executed by PHPUnit.  You can exclude files by using
the environment variables described in this [help document](IgnoringFiles.md) in the section about
command specific ignores.

## Coveralls integration

Text based coverage and a coverage XML file are fine, but how about a detailed report that tracks coverage over time?
To do exactly that, this project supports an integration with [Coveralls](https://coveralls.io), though you could
technically take the `coverage.xml` file and upload it to any service you like.

If you would like to use Coveralls, then go to https://coveralls.io and login with your GitHub account.  You will
need to authorize access to your public repositories.  Once you have done that, navigate to your
[REPOS](https://coveralls.io/repos) listing and use the _ADD REPOS_ button in the upper right to turn on your project.

Then, you need to instruct to your favourite CI tool, so the `phpunit` command generates the clover coverage file and, then, use the `coveralls-upload` command to actually upload the coverage.

Some examples follow:

#### GitHub Actions

```yaml
      - name: PHPUnit tests
        if: ${{ always() }}
        run: |
          moodle-plugin-ci phpunit --coverage-clover
          moodle-plugin-ci coveralls-upload
        env:
          COVERALLS_REPO_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```
Note that, instead of the automatically generated for every run `${{ secrets.GITHUB_TOKEN }}` token you can use any other GitHub token (PAT...) with the correct perms or, also, the token that Coveralls offers to you for every repository. Just it's easier to use the automatic GitHub one, because that way you don't need to create tokens or secrets manually and maintain them.

#### Travis

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
