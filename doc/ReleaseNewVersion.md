# Releasing a new version

This is a guide on how to release a new version of this project. Remember that when considering the version number
to use, that this project follows [Semantic Versioning](http://semver.org/), so bump the version number accordingly.

Prior to tagging a release, ensure the following have been updated:

* The `CHANGELOG.md` needs to be up-to-date.  In addition, the _Unreleased_ section needs to be updated
  with the version being released.  Also update the _Unreleased_ link at the bottom with the new version number.
* The `bin/moodle-plugin-ci` needs to be updated with the new version.
* If this is a new major version, then the `.travis.dist.yml` and `doc/TravisFileExplained.md` need to be updated
  to use the new major version.  Any other version will automatically be used.

As of writing this, releases are made from the `master` branch.  To tag a release, use a commands like the following:

```bash
$ git tag -a 1.0.0 -m "Release version 1.0.0"
$ git push origin 1.0.0
```