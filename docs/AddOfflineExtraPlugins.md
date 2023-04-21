---
layout: page
title: Adding offline extra plugins
---

This plugin is targeting devs who have a local dev environment and want to check the CI status without pushing and congesting
the Gitlab/Github CI Runner. This command will take a absolute/relative path on your device to a directory of your plugins 
and save it - exactly like add-plugin but without git clone - into the storage folder.

```yaml
  - moodle-plugin-ci add-offline-plugin --source /home/user/Documents/moodle-dev/local/<local-plugin-name>
  - moodle-plugin-ci install
```

If you cloned your plugins (and want to check out local branches) you can do that as well. There is a possibility to checkout
a branch in the storage folder (it will not touch your actual repo). It copies your .git folder and after that, it will change
your branch. The storage path is the path to the folder, your plugin is copied to before installing. It works exactly like
add-plugin after the successful execution.

```yaml
install:
  - moodle-plugin-ci add-offline-plugin --branch dev-testing --source --source <path-to-plugin> --storage moodle-plugin-ci-plugins
  - moodle-plugin-ci install
```