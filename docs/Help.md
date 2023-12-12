---
layout: page
title: Help
---

## Change log

Always a good idea to check the [change log](CHANGELOG.md) if something suddenly breaks or behaviour changed.  Also a good place to look for new goodies.

## Help topics

* [GitHub Actions workflow file explained](GHAFileExplained.md): every line of the `gha.dist.yml` file explained.
* [Travis CI file explained](TravisFileExplained.md): every line of the `.travis.dist.yml` file explained.
* [Add extra Moodle configs](AddExtraConfig.md): how to add extra configs to Moodle `config.php`.
* [Add extra plugins](AddExtraPlugins.md): how to add plugin dependencies to Moodle.
* [Ignoring files](IgnoringFiles.md): how to ignore files that might be causing failures.
* [Generating code coverage](CodeCoverage.md): how to generate code coverage of your plugin.
* [CLI commands and options](CLI.md): the available `moodle-plugin-ci` commands and their options.
* [Moodle App](MoodleApp.md): how to configure `moodle-plugin-ci` to test plugins with mobile support.

## Test steps quick start

Below is short reference to steps you may find useful to optimise set of test steps you want to
inclide in the CI scenario. For detailed information, see [CLI commands and options](CLI.md) manual.

**moodle-plugin-ci phplint**

This step lints your PHP files to check for syntax errors.

**moodle-plugin-ci phpmd**

This step runs the PHP Mess Detector on your plugin. This helps to find
potential problems with your code which can result in refactoring
opportunities.

**moodle-plugin-ci codechecker**

This step runs the Moodle Code Checker to make sure that your
plugin conforms to the Moodle coding standards.  It is highly recommended that
you keep this step.  To fail on warnings use `--max-warnings 0`

**moodle-plugin-ci phpdoc**

This step runs Moodle PHPDoc checker on your plugin. To fail on warnings use `--max-warnings 0`

**moodle-plugin-ci validate**

This step runs some light validation on the plugin file structure
and code.  Validation can be plugin specific.

**moodle-plugin-ci savepoints**

This step validates your plugin's upgrade steps.

**moodle-plugin-ci mustache**

This step validates the HTML and Javascript in your Mustache templates.

**moodle-plugin-ci grunt**

This step runs Grunt tasks on the plugin.  By default, it tries to run tasks
relevant to your plugin and Moodle version, but you can run specific tasks by
passing them as options, e.g.: `moodle-plugin-ci grunt -t task1 -t task2` To
fail on eslint warnings use `--max-lint-warnings 0`

**moodle-plugin-ci phpunit**

This step runs the PHPUnit tests of your plugin.  If your plugin has
PHPUnit tests, then it is highly recommended that you keep this step.

**moodle-plugin-ci behat**

This step runs the Behat tests of your plugin.  If your plugin has
Behat tests, then it is highly recommended that you keep this step.
There are few important options that you may want to use:
- The auto rerun option allows you to rerun failures X number of times,
  default is 2, EG usage: `--auto-rerun 3`
- The dump option allows you to print the failure HTML to the console,
  handy for debugging, e.g. usage: `--dump`
- The suite option allows you to set the theme to use for behat test. If
  not specified, the default theme is used, e.g. usage: `--suite boost`
- The profile option allows you to set the browser driver to use,
  default is Firefox (or Chrome when `MOODLE_APP` is set). If you need a specific browser,
  use `--profile chrome` or `--profile firefox`.
- The tags option allows you to specify which scenarios to run filtered by tags,
  default is the tag with the plugin's component name, e.g. usage: `--tags="@local_myplugin~@ciskip"`

## Upgrade guides

* [Upgrading to Version 4](UPGRADE-4.0.md)
* [Upgrading to Version 3](UPGRADE-3.0.md)
* [Upgrading to Version 2](UPGRADE-2.0.md)


## Other help

If the above links do not help you, and Google has failed you as well, then please feel free
to submit an [new issue](https://github.com/moodlehq/moodle-plugin-ci/issues/new) providing
as many relevant details as possible.

Also, it is sometimes useful to dig into source code of `moodle-plugin-ci`, this may
help to understand what is done on background and identify why something does
not work as expected for you.
