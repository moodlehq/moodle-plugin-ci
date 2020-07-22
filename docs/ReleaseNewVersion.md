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
to get them reviewed and incorporated upstream, **without merge commits** better).

Once all code and commits are in place and verified, to tag a release, use some commands like the following:

```bash
$ git tag -a 1.0.0 -m "Release version 1.0.0"
$ git push origin 1.0.0
```

When the tag is pushed, travis should automatically proceed to create the `moodle-plugin-ci.phar` release artifact
and add it to the release files. Verify it has worked ok.

If there is any problem with that automatic deployment, the artifact will need to be created manually, follow these steps:

- TODO
- WIP

