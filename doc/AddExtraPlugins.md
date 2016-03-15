# Adding extra plugins

Sometimes the plugin that you are testing may depend on another plugin or even several other plugins.  This project
provides a way to Git clone the extra plugins and add them to the Moodle test site. Here is an example of how to use
it in your `.travis.yml` file:

```yml
install:
  - moodle-plugin-ci add-plugin moodlehq/moodle-local_hub
  - moodle-plugin-ci install
```

You may add as many plugins as you like, by simply calling the `add-plugin` command for each plugin.  The `add-plugin`
command takes a single argument and that is your GitHub account name and the project name.  So, in the example, it
would clone `https://github.com/moodlehq/moodle-local_hub.git`.

By default, the branch that is cloned is the `master` branch.  You can use the `--branch` (`-b`) option to override
this behavior.  If you use the same branch names as Moodle (EG: `MOODLE_XY_STABLE`), then a handy trick is to pass
the `$MOODLE_BRANCH` build variable to the `add-plugin` command.  Here is an example:

```yml
install:
  - moodle-plugin-ci add-plugin --branch $MOODLE_BRANCH username/project
  - moodle-plugin-ci install
```

If you are not using GitHub and want to provide your own Git clone URL, then you can use the `--clone` (`-c`) option.
Here is an example (Note, you can use the `--branch` option together with the `--clone` option if you need to):

```yml
install:
  - moodle-plugin-ci add-plugin --clone https://bitbucket.org/username/project.git
  - moodle-plugin-ci install
```
