# Adding extra configs to Moodle's configuration file

Sometimes a plugin may require extra config in the Moodle `config.php` file because, for example, it interacts with a
non-standard service.  This project provides a way to update the `config.php` file with the `add-config` command.  Here
is an example of how to use it in your `.travis.yml` file:

```yml
install:
  - moodle-plugin-ci install
  - moodle-plugin-ci add-config '$CFG->foo = "bar";'
  - moodle-plugin-ci add-config 'define("BAT", "baz");'
```

As you may have noticed, the `add-config` command takes a single argument which is a valid line of PHP code.
The first `add-config` call will add `$CFG->foo = "bar";` to the config file and the second will add
`define("BAT", "baz");` to the config file, after our first line.

Some things to keep in mind when crafting these calls to `add-config`:
* You might be tempted to reverse the quotes because normally single quotes are used for strings in PHP.  While this is
  true, we are also using Bash here and using double quotes will cause several problems.  In particular, Bash will try
  to evaluate the string and replace things like `$CFG` with a Bash variable.
* If you are having problems with the command parsing the argument as an option, you can use the following form:
  `moodle-plugin-ci add-config -- yourArgument`.  The double dash stops the processing of options.
* Don't forget to include the semicolon at the end of your line of PHP code!