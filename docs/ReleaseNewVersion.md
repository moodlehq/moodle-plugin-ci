---
layout: page
title: Releasing a new version
---

This is a guide on how to release a new version of this project. Remember that when considering the version number
to use, that this project follows [Semantic Versioning](http://semver.org/), so bump the version number accordingly.

Prior to tagging a release, ensure the following have been updated:

* The `CHANGELOG.md` needs to be up-to-date.  In addition, the _Unreleased_ section needs to be updated
  with the version being released.  Also update the _Unreleased_ link at the bottom with the new version number.
* The `bin/moodle-plugin-ci` also needs to be updated accordingly, setting the `MOODLE_PLUGIN_CI_VERSION` constant
  to the version being released.
* If this is a new major version, then CI tool example files and its docs need
  to be updated to use the new major version (e.g. for Travis CI make changes
  in `.travis.dist.yml` and `doc/TravisFileExplained.md`). Any other version
  will automatically be used.

When all the changes above have been performed, push them straight upstream to
`master` or create a standard PR to get them reviewed and incorporated
upstream. In the latter case, use your repo clone for PR - this way Packagist
will not capture new branch in the list of tags and you will not end up having
double CI build on Travis, also when merging PR, please **avoid merge commit**
(use "Rebase and merge" option).

Once all code and commits are in place and verified, you need to tag a
release. Tag `master` branch `HEAD` and push using commands:

```bash
$ git tag -a 3.1.0 -m "Release version 3.1.0"
$ git push origin 3.1.0
```

(while it's also possible to use GitHub interface, we have decided not to do
so, GitHub release action will, automatically, create the needed artifacts and
perform the release)

When the tag is pushed, GitHub release action will be triggered.  At this
stage it should automatically create the `moodle-plugin-ci.phar` release
artifact and add it to the latest release "assets" on GitHub. Verify it has
worked correctly by navigating at
[Releases](https://github.com/moodlehq/moodle-plugin-ci/releases).

While in that page, optionally, you can edit the release and add any content
to the description (links to the changelog / upgrade docs are added
automatically but anything important can be commented if needed to).

If there is any problem with that automatic deployment, the artifact will need
to be created manually. First build PHAR file manually:

```bash
$ make build
```

Once the above command suceeded, navigate to latest release in [GitHub
interface](https://github.com/moodlehq/moodle-plugin-ci/releases), click
"Edit" and attach generated PHAR file to release (you will find it at `./build` subdir).
