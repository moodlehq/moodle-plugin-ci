---
layout: page
title: Introduction
---

The goal of this project is to facilitate the running of tests and code
analysis against a Moodle plugin in CI tool of your choice. All of these tests
and tools are run everytime a change is pushed to a GitHub branch or pull
request.

We currently provide user manual how to use it with [GitHub
Actions](https://docs.github.com/en/actions) and [Travis
CI](https://travis-ci.com), if you are using
`moodle-plugin-ci` with other CI services please do share your setup examples
by creating a ticket.

Why would you want to do this?  It saves you from having to remember to setup and run PHPUnit, Behat, code checker, etc
every single time you make a change.  If you have enough test coverage, it also makes accepting pull requests painless
because you can be more confident that the change wont break anything.  There are many more advantages to use CI
tools, like being able to test your code against multiple databases, multiple PHP versions, etc.

This project supports the following testing frameworks and code analysis tools:

* [PHPUnit](https://phpunit.de)
* [Behat](http://behat.org/)
* [Moodle Code Checker](https://github.com/moodlehq/moodle-local_codechecker)
* [Moodle PHPdoc check](https://github.com/moodlehq/moodle-local_moodlecheck)
* [Mustache Linting](https://docs.moodle.org/dev/Templates)
* [Grunt tasks](https://docs.moodle.org/dev/Grunt)
* [PHP Linting](https://github.com/JakubOnderka/PHP-Parallel-Lint)
* [PHP Mess Detector](http://phpmd.org)

## Requirements

The requirements for **Version 4** are **PHP 7.4** or later and **Moodle 3.8.3** or later.

In addition, the plugin being tested must have a [version.php](https://docs.moodle.org/dev/version.php) file
and `$plugin->component` must be defined within it.

If you need to run your plugin in earlier versions of Moodle, then please use previous versions of this tool.  Documentation
and more [information about Version 3 can be found here](https://github.com/moodlehq/moodle-plugin-ci/tree/3.x/docs/index.md).

Please know older versions (1, 2 and 3) are no longer getting new features and may not receive additional updates.

## Getting started

Follow steps to get your Moodle plugin building in CI tool of your choice.

### GitHub Actions

#### Step 1

Copy [gha.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/gha.dist.yml) file to `.github/workflows` directory
in your project. You can give this file any name, e.g. `moodle-ci.yml` which we use in this document. Review the `moodle-ci.yml` contents and
remove anything that is not needed.  See this [help document](GHAFileExplained.md) for an explanation about the
contents of the this file. Once you have added the `.github/workflows/moodle-ci.yml` file, commit and push up to GitHub, to trigger a
CI build.

#### Step 2

On GitHub, navigate to the main page of the repository. Under your repository
name, click Actions tab and see your build status.  You may find this [Quick
Start manual](https://docs.github.com/en/actions/quickstart#viewing-your-workflow-results)
useful. Now whenever you push commits to your plugin repo or it gets a new
pull request, GitHub will run a build to make sure nothing broke.

### Travis CI

#### Step 1

Sign into [Travis CI](https://travis-ci.com) with your GitHub account. Once you’re signed in, and Travis CI will have
synchronized your repositories from GitHub.  Go to your [profile](https://travis-ci.com/profile) page and enable Travis CI
for the plugin you want to build.  Now whenever your plugin receives an update or gets a new pull request, Travis CI will
run a build to make sure nothing broke.

#### Step 2

Copy the [.travis.dist.yml](https://github.com/moodlehq/moodle-plugin-ci/blob/main/.travis.dist.yml) file into the
root of your plugin and rename it to `.travis.yml`. Now might be a good time to review the `.travis.yml` contents and
remove anything that is not needed.  See this [help document](TravisFileExplained.md) for an explanation about the
contents of the this file. Once you have added the `.travis.yml` file, commit and push up to GitHub, to trigger a
Travis CI build. Navigate back to [Travis CI](https://travis-ci.com) to see if your build passes or fails.

### Using in a Local Dev Environment

It is possible to use `moodle-plugin-ci` locally. It needs to be installed outside
Moodle directory, so if you are within Moodle repo, execute:

```
$ php composer.phar create-project moodlehq/moodle-plugin-ci ../moodle-plugin-ci ^4
```

Once installed, you can run most of its commands to validate your code, e.g.

```
$ ../moodle-plugin-ci/bin/moodle-plugin-ci mustache ./mod/forum/
 RUN  Mustache Lint on mod_forum
/home/ruslan/git/moodledev/mod/forum/templates/quick_search_form.mustache - OK: Mustache rendered html succesfully
/home/ruslan/git/moodledev/mod/forum/templates/single_discussion_list.mustache - OK: Mustache rendered html succesfully
...
```

Alternatively, one can use phar package obtained from the release page, this can
be downloaded safely to Moodle directory (it is in Moodle's `.gitignore`):

```
$ wget https://github.com/moodlehq/moodle-plugin-ci/releases/download/4.1.6/moodle-plugin-ci.phar
```

Execute it in this case as follows:

```
$ php moodle-plugin-ci.phar mustache ./mod/forum/
 RUN  Mustache Lint on mod_forum
/home/ruslan/git/moodledev/mod/forum/templates/quick_search_form.mustache - OK: Mustache rendered html succesfully
/home/ruslan/git/moodledev/mod/forum/templates/single_discussion_list.mustache - OK: Mustache rendered html succesfull
```

Notice that using this approach allows you running CI commands on plugins only.

#### Using PHP CodeSniffer and PHP Code Beautifier and Fixer

It is possible to use CodeSniffer from the package on individual files and directories, they don't have to be a part of plugin in this case. You need to have it installed using composer approach.

```
$ ../moodle-plugin-ci/vendor/bin/phpcs ./index.php

FILE: /home/ruslan/git/moodledev/index.php
-------------------------------------------------------------------------------------------------------------
FOUND 2 ERRORS AND 3 WARNINGS AFFECTING 5 LINES
-------------------------------------------------------------------------------------------------------------
 25 | ERROR   | [ ] Expected MOODLE_INTERNAL check or config.php inclusion. Change in global state detected.
 36 | WARNING | [x] Short array syntax must be used to define arrays
 58 | ERROR   | [ ] Logical operator "and" is prohibited; use "&&" instead
 86 | WARNING | [x] Short array syntax must be used to define arrays
 92 | WARNING | [x] Short array syntax must be used to define arrays
-------------------------------------------------------------------------------------------------------------
PHPCBF CAN FIX THE 3 MARKED SNIFF VIOLATIONS AUTOMATICALLY
-------------------------------------------------------------------------------------------------------------

Time: 197ms; Memory: 16MB
```

From the example above, PHP Code Beautifier and Fixer may address 3 violations
automatically, saving developer time:

```
$ ../moodle-plugin-ci/vendor/bin/phpcbf ./index.php

PHPCBF RESULT SUMMARY
----------------------------------------------------------------------
FILE                                                  FIXED  REMAINING
----------------------------------------------------------------------
/home/ruslan/git/moodledev/index.php                  3      2
----------------------------------------------------------------------
A TOTAL OF 3 ERRORS WERE FIXED IN 1 FILE
----------------------------------------------------------------------

Time: 328ms; Memory: 16MB
```

### Getting more of CI

Next steps on your continuous build journey may include:

* Reviewing the [help documentation](Help.md) to further improve and customize your build.
* Resolve any build errors you may currently have. Get to that ever rewarding Green Build status.
* Show off your build status by adding the build status badge to your plugin's README file.
* Learn how to [configure CI for your mobile plugins in the Moodle App](MoodleApp.md).
* Write new tests to increase your code coverage.
* Contribute to this repo if you have improvement idea or found an issue.
* Enjoy your favorite beverage because you no longer have to waste time manually testing your plugin!

## Upgrading

Guides to updating your plugin's `.travis.yml` file to use the latest versions of this tool.

* [Upgrade to 4.0](UPGRADE-4.0.md)
* [Upgrade to 3.0](UPGRADE-3.0.md)
* [Upgrade to 2.0](UPGRADE-2.0.md)

## Documentation

Visit the [help page](Help.md) for a complete list of documents, guides and links.

## Usage / Versioning

This project uses [Semantic Versioning](http://semver.org/) for its public API.  The public API for this project
is defined as the CLI interface of the `moodle-plugin-ci` script.  _Everything_ outside of this script is considered
to be private API and is not guaranteed to follow Semantic Versioning.

The commands ran via the `moodle-plugin-ci` script by default take no arguments.  It is recommended, if at all possible,
to avoid using arguments to prevent problems with future versions.  Rest assured though that if any arguments are
modified, it will be in the [change log](CHANGELOG.md) and the version will be bumped appropriately according to
Semantic Versioning.

## License

This project is licensed under the GNU GPL v3 or later.  See the
[LICENSE](https://github.com/moodlehq/moodle-plugin-ci/blob/main/LICENSE) file for details.
