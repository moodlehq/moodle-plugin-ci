---
layout: page
title: Moodle Plugin CI Commands
---

<!-- AUTOMATICALLY GENERATED VIA: make docs/CLI.md -->

* [`add-config`](#add-config)
* [`add-plugin`](#add-plugin)
* [`behat`](#behat)
* [`codechecker`](#phpcs)
* [`codefixer`](#phpcbf)
* [`completion`](#completion)
* [`coveralls-upload`](#coveralls-upload)
* [`grunt`](#grunt)
* [`help`](#help)
* [`install`](#install)
* [`list`](#list)
* [`mustache`](#mustache)
* [`parallel`](#parallel)
* [`phpcbf`](#phpcbf)
* [`phpcs`](#phpcs)
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
* Is negatable: no
* Default: `'.'`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`add-plugin`
------------

Queue up an additional plugin to be installed in the test site

### Usage

* `add-plugin [-b|--branch BRANCH] [-c|--clone CLONE] [--storage STORAGE] [--] [<project>]`

Queue up an additional plugin to be installed in the test site

### Arguments

#### `project`

GitHub project, EG: moodlehq/moodle-local_hub, can't be used with --clone option

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--branch|-b`

The branch to checkout in plugin repo (if non-default)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--clone|-c`

Git clone URL, can't be used with --project option

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--storage`

Plugin storage directory

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'moodle-plugin-ci-plugins'`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`behat`
-------

Run Behat on a plugin

### Usage

* `behat [-m|--moodle MOODLE] [-p|--profile PROFILE] [--suite SUITE] [--tags TAGS] [--name NAME] [--start-servers] [--auto-rerun AUTO-RERUN] [--selenium SELENIUM] [--dump] [--] <plugin>`

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
* Is negatable: no
* Default: `'.'`

#### `--profile|-p`

Behat profile option to use

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'default'`

#### `--suite`

Behat suite option to use (Moodle theme)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'default'`

#### `--tags`

Behat tags option to use. If not set, defaults to the component name

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `''`

#### `--name`

Behat name option to use

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `''`

#### `--start-servers`

Start Selenium and PHP servers

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--auto-rerun`

Number of times to rerun failures

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `2`

#### `--selenium`

Selenium Docker image

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--dump`

Print contents of Behat failure HTML files

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`completion`
------------

Dump the shell completion script

### Usage

* `completion [--debug] [--] [<shell>]`

The completion command dumps the shell completion script required
to use shell autocompletion (currently, bash, fish, zsh completion are supported).

Static installation
-------------------

Dump the script to a global completion file and restart your shell:

    bin/moodle-plugin-ci completion bash | sudo tee /etc/bash_completion.d/moodle-plugin-ci

Or dump the script to a local file and source it:

    bin/moodle-plugin-ci completion bash > completion.sh

    # source the file whenever you use the project
    source completion.sh

    # or add this line at the end of your "~/.bashrc" file:
    source /path/to/completion.sh

Dynamic installation
--------------------

Add this to the end of your shell configuration file (e.g. "~/.bashrc"):

    eval "$(/path/to/bin/moodle-plugin-ci completion bash)"

### Arguments

#### `shell`

The shell type (e.g. "bash"), the value of the "$SHELL" env var will be used if this is not given

* Is required: no
* Is array: no
* Default: `NULL`

### Options

#### `--debug`

Tail the completion debug log

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
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
* Is negatable: no
* Default: `'./coverage.xml'`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
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
* Is negatable: no
* Default: `'.'`

#### `--tasks|-t`

The Grunt tasks to run

* Accept value: yes
* Is value required: yes
* Is multiple: yes
* Is negatable: no
* Default: `array (  0 => 'amd',  1 => 'yui',  2 => 'gherkinlint',  3 => 'stylelint:css',  4 => 'stylelint:less',  5 => 'stylelint:scss',)`

#### `--show-lint-warnings`

Show eslint warnings

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--max-lint-warnings`

Maximum number of eslint warnings

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `''`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`help`
------

Display help for a command

### Usage

* `help [--format FORMAT] [--raw] [--] [<command_name>]`

The help command displays help for a given command:

  bin/moodle-plugin-ci help list

You can also output the help in other formats by using the --format option:

  bin/moodle-plugin-ci help --format=xml list

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
* Is negatable: no
* Default: `'txt'`

#### `--raw`

To output raw command help

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`install`
---------

Install everything required for CI testing

### Usage

* `install [--moodle MOODLE] [--data DATA] [--repo REPO] [--branch BRANCH] [--plugin PLUGIN] [--db-type DB-TYPE] [--db-user DB-USER] [--db-pass DB-PASS] [--db-name DB-NAME] [--db-host DB-HOST] [--db-port DB-PORT] [--not-paths NOT-PATHS] [--not-names NOT-NAMES] [--extra-plugins EXTRA-PLUGINS] [--no-init] [--no-plugin-node] [--node-version NODE-VERSION]`

Install everything required for CI testing

### Options

#### `--moodle`

Clone Moodle to this directory

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'moodle'`

#### `--data`

Directory create for Moodle data files

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'moodledata'`

#### `--repo`

Moodle repository to clone

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'https://github.com/moodle/moodle.git'`

#### `--branch`

Moodle git branch to clone, EG: MOODLE_29_STABLE

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--plugin`

Path to Moodle plugin

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--db-type`

Database type, mysqli, pgsql or mariadb

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--db-user`

Database user

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--db-pass`

Database pass

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `''`

#### `--db-name`

Database name

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'moodle'`

#### `--db-host`

Database host

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'localhost'`

#### `--db-port`

Database port

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `''`

#### `--not-paths`

CSV of file paths to exclude

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--not-names`

CSV of file names to exclude

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--extra-plugins`

Directory of extra plugins to install

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--no-init`

Prevent PHPUnit and Behat initialization

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--no-plugin-node`

Prevent Node.js plugin dependencies installation

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--node-version`

Node.js version to use for nvm install (this will override one defined in .nvmrc)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`list`
------

List commands

### Usage

* `list [--raw] [--format FORMAT] [--short] [--] [<namespace>]`

The list command lists all commands:

  bin/moodle-plugin-ci list

You can also display the commands for a specific namespace:

  bin/moodle-plugin-ci list test

You can also output the information in other formats by using the --format option:

  bin/moodle-plugin-ci list --format=xml

It's also possible to get raw list of commands (useful for embedding command runner):

  bin/moodle-plugin-ci list --raw

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
* Is negatable: no
* Default: `false`

#### `--format`

The output format (txt, xml, json, or md)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `'txt'`

#### `--short`

To skip describing commands' arguments

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

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
* Is negatable: no
* Default: `'.'`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
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
* Is negatable: no
* Default: `'.'`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`phpcbf`
--------

Run Code Beautifier and Fixer on a plugin

### Usage

* `phpcbf [-s|--standard STANDARD] [--] <plugin>`
* `codefixer`

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
* Is negatable: no
* Default: `'moodle'`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`phpcs`
-------

Run Moodle CodeSniffer standard on a plugin

### Usage

* `phpcs [-s|--standard STANDARD] [-x|--exclude EXCLUDE] [--max-warnings MAX-WARNINGS] [--test-version TEST-VERSION] [--todo-comment-regex TODO-COMMENT-REGEX] [--license-regex LICENSE-REGEX] [--] <plugin>`
* `codechecker`

Run Moodle CodeSniffer standard on a plugin

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
* Is negatable: no
* Default: `'moodle'`

#### `--exclude|-x`

Comma separated list of sniff codes to exclude from checking

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `''`

#### `--max-warnings`

Number of warnings to trigger nonzero exit code - default: -1

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `-1`

#### `--test-version`

Version or range of version to test with PHPCompatibility

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `0`

#### `--todo-comment-regex`

Regex to use to match TODO/@todo comments

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `''`

#### `--license-regex`

Regex to use to match @license tags

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `''`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`phpdoc`
--------

Run Moodle PHPDoc Checker on a plugin

### Usage

* `phpdoc [-m|--moodle MOODLE] [--max-warnings MAX-WARNINGS] [--] <plugin>`

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
* Is negatable: no
* Default: `'.'`

#### `--max-warnings`

Number of warnings to trigger nonzero exit code - default: -1

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `-1`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
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

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
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
* Is negatable: no
* Default: `'.'`

#### `--rules|-r`

Path to PHP Mess Detector rule set

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

`phpunit`
---------

Run PHPUnit on a plugin

### Usage

* `phpunit [-m|--moodle MOODLE] [-c|--configuration CONFIGURATION] [--testsuite TESTSUITE] [--filter FILTER] [--testdox] [--coverage-text] [--coverage-clover] [--coverage-pcov] [--coverage-xdebug] [--coverage-phpdbg] [--fail-on-incomplete] [--fail-on-risky] [--fail-on-skipped] [--fail-on-warning] [--] <plugin>`

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
* Is negatable: no
* Default: `'.'`

#### `--configuration|-c`

PHPUnit configuration XML file (relative to plugin directory)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--testsuite`

PHPUnit testsuite option to use (must exist in the configuration file being used)

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--filter`

PHPUnit filter option to use

* Accept value: yes
* Is value required: yes
* Is multiple: no
* Is negatable: no
* Default: `NULL`

#### `--testdox`

Enable testdox formatter

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--coverage-text`

Generate and print code coverage report in text format

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--coverage-clover`

Generate code coverage report in Clover XML format

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--coverage-pcov`

Use the pcov extension to calculate code coverage

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--coverage-xdebug`

Use the xdebug extension to calculate code coverage

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--coverage-phpdbg`

(**DEPRECATED**) Use the phpdbg binary to calculate code coverage

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--fail-on-incomplete`

Treat incomplete tests as failures

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--fail-on-risky`

Treat risky tests as failures

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--fail-on-skipped`

Treat skipped tests as failures

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--fail-on-warning`

Treat tests with warnings as failures

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
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

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
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
* Is negatable: no
* Default: `'.'`

#### `--help|-h`

Display help for the given command. When no command is given display help for the list command

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--quiet|-q`

Do not output any message

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--verbose|-v|-vv|-vvv`

Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--version|-V`

Display this application version

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`

#### `--ansi|--no-ansi`

Force (or disable --no-ansi) ANSI output

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: yes
* Default: `NULL`

#### `--no-interaction|-n`

Do not ask any interactive question

* Accept value: no
* Is value required: no
* Is multiple: no
* Is negatable: no
* Default: `false`