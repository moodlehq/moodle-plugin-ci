# Contributing to Moodle Plugin CI

Please note that this project is released with a
[Contributor Code of Conduct](http://contributor-covenant.org/version/1/2/0/). By participating in this project you 
agree to abide by its terms.

# Reporting Issues

When reporting issues, please try to be as descriptive as possible, and include as much relevant information as you
can. A step by step guide on how to reproduce the issue will greatly increase the chances of your issue being
resolved in a timely manner.

For example, if you are experiencing a problem while running one of the commands, please provide full output of said
command in debug mode by using the `-vvv` option. EG: `moodle-plugin-ci install -vvv`

# Contributing policy

Fork the project, create a feature branch, and send us a pull request.  Prior to submitting the pull request,
please ensure that these commands are ran successfully:

``` bash
$ composer fix
$ composer lint
$ composer test
```

Ways to improve your chances of getting your contribution accepted:
* Add tests when adding a new feature or fixing a bug.
* Only one pull request per feature or fix.
* Send coherent history by making sure each individual commit in your pull request is meaningful. Use interactive
  rebase to squash intermediate commits.
* Ensure that [read me](README.md), [change log](CHANGELOG.md) and [other docs](doc) are updated as needed.
