---
layout: page
title: Help
---

## Change log

Always a good idea to check the [change log](CHANGELOG.md) if something suddenly breaks or behavior
changed.  Also a good place to look for new goodies.

## Help topics

* [Travis CI file explained](TravisFileExplained.md): every line of the `.travis.dist.yml` file explained.
* [GitHub Actions workflow file explained](GHAFileExplained.md): every line of the `gha.dist.yml` file explained.
* [Add extra Moodle configs](AddExtraConfig.md): how to add extra configs to Moodle `config.php`.
* [Add extra plugins](AddExtraPlugins.md): how to add plugin dependencies to Moodle.
* [Ignoring files](IgnoringFiles.md): how to ignore files that might be causing failures.
* [Generating code coverage](CodeCoverage.md): how to generate code coverage of your plugin.
* [CLI commands and options](CLI.md): the available `moodle-plugin-ci` commands and their options.

## Upgrade guides

* [Upgrading to Version 3](UPGRADE-3.0.md)
* [Upgrading to Version 2](UPGRADE-2.0.md)


## Other help

If the above links do not help you, and Google has failed you as well, then please feel free
to submit an [new issue](https://github.com/moodlehq/moodle-plugin-ci/issues/new) providing
as many relevant details as possible.

Also, it is sometimes useful to dig into source code of `moodle-plugin-ci`, this may
help to understand what is done on background and identify why something does
not work as expected for you.
