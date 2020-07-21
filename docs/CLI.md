---
layout: page
title: Moodle Plugin CI Commands
---

<!-- AUTOMATICALLY GENERATED VIA: make docs/CLI.md -->

* [`add-config`](#add-config)
* [`add-plugin`](#add-plugin)
* [`behat`](#behat)
* [`codechecker`](#codechecker)
* [`coveralls-upload`](#coveralls-upload)
* [`grunt`](#grunt)
* [`help`](#help)
* [`install`](#install)
* [`list`](#list)
* [`mustache`](#mustache)
* [`parallel`](#parallel)
* [`phpcbf`](#phpcbf)
* [`phpcpd`](#phpcpd)
* [`phpdoc`](#phpdoc)
* [`phplint`](#phplint)
* [`phpmd`](#phpmd)
* [`phpunit`](#phpunit)
* [`savepoints`](#savepoints)
* [`validate`](#validate)

`add-config`
------------

Add a line to the Moodle config.php file

### Usage

* `add-config [-m|--moodle MOODLE] [--] <line>`

Add a line to the Moodle config.php file

### Arguments

#### `line`

Line of PHP code to add to the Moodle config.php file

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`add-plugin`
------------

Queue up an additional plugin to be installed in the test site

### Usage

* `add-plugin [-b|--branch BRANCH] [-c|--clone CLONE] [--storage STORAGE] [--] [<project>]`

Queue up an additional plugin to be installed in the test site

### Arguments

#### `project`

GitHub project, EG: moodlehq/moodle-local_hub

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--branch|-b`

The branch to checkout within the plugin

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'master'`

#### `--clone|-c`

Git clone URL

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--storage`

Plugin storage directory

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'moodle-plugin-ci-plugins'`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`behat`
-------

Run Behat on a plugin

### Usage

* `behat [-m|--moodle MOODLE] [-p|--profile PROFILE] [--suite SUITE] [--start-servers] [--auto-rerun AUTO-RERUN] [--dump] [--] <plugin>`

Run Behat on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--profile|-p`

Behat profile to use

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'default'`

#### `--suite`

Behat suite to use (Moodle theme)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'default'`

#### `--start-servers`

Start Selenium and PHP servers

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--auto-rerun`

Number of times to rerun failures

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `2`

#### `--dump`

Print contents of Behat failure HTML files

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`codechecker`
-------------

Run Moodle Code Checker on a plugin

### Usage

* `codechecker [-s|--standard STANDARD] [--max-warnings MAX-WARNINGS] [--] <plugin>`

Run Moodle Code Checker on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--standard|-s`

The name or path of the coding standard to use

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'moodle'`

#### `--max-warnings`

Number of warnings to trigger nonzero exit code - default: -1

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `-1`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`coveralls-upload`
------------------

Upload code coverage to Coveralls

### Usage

* `coveralls-upload [--coverage-file COVERAGE-FILE] [--] <plugin>`

Upload code coverage to Coveralls

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--coverage-file`

Location of the Clover XML file to upload

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'./coverage.xml'`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`grunt`
-------

Run Grunt task on a plugin

### Usage

* `grunt [-m|--moodle MOODLE] [-t|--tasks TASKS] [--show-lint-warnings] [--max-lint-warnings MAX-LINT-WARNINGS] [--] <plugin>`

Run Grunt task on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--tasks|-t`

The Grunt tasks to run

* Accept value: yes
* Is value required: yes
* Is multiple: yes
* Default: `array (  0 => 'amd',  1 => 'yui',  2 => 'gherkinlint',  3 => 'stylelint:css',  4 => 'stylelint:less',  5 => 'stylelint:scss',)`

#### `--show-lint-warnings`

Show eslint warnings

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--max-lint-warnings`

Maximum number of eslint warnings

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `''`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`help`
------

Displays help for a command

### Usage

* `help [--format FORMAT] [--raw] [--] [<command_name>]`

The help command displays help for a given command:

  php bin/moodle-plugin-ci help list

You can also output the help in other formats by using the --format option:

  php bin/moodle-plugin-ci help --format=xml list

To display the list of available commands, please use the list command.

### Arguments

#### `command_name`

The command name

* Is required: no
* Is array: no
* Default: `'help'`

### Options

#### `--format`

The output format (txt, xml, json, or md)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'txt'`

#### `--raw`

To output raw command help

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`install`
---------

Install everything required for CI testing

### Usage

* `install [--moodle MOODLE] [--data DATA] [--repo REPO] [--branch BRANCH] [--plugin PLUGIN] [--db-type DB-TYPE] [--db-user DB-USER] [--db-pass DB-PASS] [--db-name DB-NAME] [--db-host DB-HOST] [--not-paths NOT-PATHS] [--not-names NOT-NAMES] [--extra-plugins EXTRA-PLUGINS] [--no-init] [--node-version NODE-VERSION]`

Install everything required for CI testing

### Options

#### `--moodle`

Clone Moodle to this directory

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'moodle'`

#### `--data`

Directory create for Moodle data files

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'moodledata'`

#### `--repo`

Moodle repository to clone

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'git://github.com/moodle/moodle.git'`

#### `--branch`

Moodle git branch to clone, EG: MOODLE_29_STABLE

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--plugin`

Path to Moodle plugin

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--db-type`

Database type, mysqli, pgsql or mariadb

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--db-user`

Database user

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--db-pass`

Database pass

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `''`

#### `--db-name`

Database name

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'moodle'`

#### `--db-host`

Database host

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'localhost'`

#### `--not-paths`

CSV of file paths to exclude

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--not-names`

CSV of file names to exclude

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--extra-plugins`

Directory of extra plugins to install

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--no-init`

Prevent PHPUnit and Behat initialization

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--node-version`

Node.js version to use for nvm install (this will override one defined in .nvmrc)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`list`
------

Lists commands

### Usage

* `list [--raw] [--format FORMAT] [--] [<namespace>]`

The list command lists all commands:

  php bin/moodle-plugin-ci list

You can also display the commands for a specific namespace:

  php bin/moodle-plugin-ci list test

You can also output the information in other formats by using the --format option:

  php bin/moodle-plugin-ci list --format=xml

It's also possible to get raw list of commands (useful for embedding command runner):

  php bin/moodle-plugin-ci list --raw

### Arguments

#### `namespace`

The namespace name

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--raw`

To output raw command list

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--format`

The output format (txt, xml, json, or md)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'txt'`

`mustache`
----------

Run Mustache Lint on a plugin

### Usage

* `mustache [-m|--moodle MOODLE] [--] <plugin>`

Run Mustache Lint on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`parallel`
----------

Run all of the tests and analysis against a plugin

### Usage

* `parallel [-m|--moodle MOODLE] [--] <plugin>`

Run all of the tests and analysis against a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`phpcbf`
--------

Run Code Beautifier and Fixer on a plugin

### Usage

* `phpcbf [-s|--standard STANDARD] [--] <plugin>`

Run Code Beautifier and Fixer on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--standard|-s`

The name or path of the coding standard to use

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'moodle'`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`phpcpd`
--------

Run PHP Copy/Paste Detector on a plugin

### Usage

* `phpcpd <plugin>`

Run PHP Copy/Paste Detector on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`phpdoc`
--------

Run Moodle PHPDoc Checker on a plugin

### Usage

* `phpdoc [-m|--moodle MOODLE] [--] <plugin>`

Run Moodle PHPDoc Checker on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`phplint`
---------

Run PHP Lint on a plugin

### Usage

* `phplint <plugin>`

Run PHP Lint on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`phpmd`
-------

Run PHP Mess Detector on a plugin

### Usage

* `phpmd [-m|--moodle MOODLE] [-r|--rules RULES] [--] <plugin>`

Run PHP Mess Detector on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--rules|-r`

Path to PHP Mess Detector rule set

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `NULL`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`phpunit`
---------

Run PHPUnit on a plugin

### Usage

* `phpunit [-m|--moodle MOODLE] [--coverage-text] [--coverage-clover] [--] <plugin>`

Run PHPUnit on a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--coverage-text`

Generate and print code coverage report in text format

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--coverage-clover`

Generate code coverage report in Clover XML format

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`savepoints`
------------

Check upgrade savepoints

### Usage

* `savepoints <plugin>`

Check upgrade savepoints

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

`validate`
----------

Validate a plugin

### Usage

* `validate [-m|--moodle MOODLE] [--] <plugin>`

Validate a plugin

### Arguments

#### `plugin`

Path to the plugin

* Is required: yes
* Is array: no
* Default: `NULL`

### Options

#### `--moodle|-m`

Path to Moodle

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Default: `'.'`

#### `--help|-h`

Display this help message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--ansi`

Force ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-ansi`

Disable ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Default: `false`