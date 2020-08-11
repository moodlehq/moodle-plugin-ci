---
layout: page
title: Releasing a new version
---

This is a guide on how to release a new version of this project. Remember that when considering the version number
to use, that this project follows [Semantic Versioning](http://semver.org/), so bump the version number accordingly.

Prior to tagging a release, ensure the following have been updated:

* The `CHANGELOG.md` needs to be up-to-date.  In addition, the _Unreleased_ section needs to be updated
  with the version being released.  Also update the _Unreleased_ link at the bottom with the new version number.
* If this is a new major version, then the `.travis.dist.yml` and `doc/TravisFileExplained.md` need to be updated
  to use the new major version.  Any other version will automatically be used.

When all the changes above have been performed, push them straight upstream to `master` (or create a standard PR
to get them reviewed and incorporated upstream, but **avoid merge commit**, use "Rebase and merge" instead).

Once all code and commits are in place and verified, you need to tag a release.

There are two options: you can tag `master` branch `HEAD` and push using commands:

```bash
$ git tag -a 3.1.0 -m "Release version 3.1.0"
$ git push origin 3.1.0
```
Alternatively, you can use GitHub interface to tag: click "Draft a new release" and specify
tag using the interface (you can leave title field empty, tag will be used as
title in this case) and click "Publish release".

In either option, when the tag is pushed or created via interface, travis will
trigger a tag build that contains Deploy stage. At this stage it should
automatically create the `moodle-plugin-ci.phar` release artifact and add it
to the latest release "assets" on GitHub. Verify it has worked correctly by
navigating at
[Releases](https://github.com/moodlehq/moodle-plugin-ci/releases).

If there is any problem with that automatic deployment, the artifact will need to be created manually. First build PHAR file manually:

```bash
$ make build/moodle-plugin-ci.phar
```

Once the above command suceeded, navigate to latest release in [GitHub
interface](https://github.com/moodlehq/moodle-plugin-ci/releases), click
"Edit" and attach generated PHAR file to release (you will find it at `./build` subdir).
