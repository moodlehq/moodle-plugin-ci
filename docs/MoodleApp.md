---
layout: page
title: Moodle App
---

In order to test plugins with mobile support, the only command that needs a special configuration is `behat`.

In practice, you only need to set the `MOODLE_APP` env variable to `true`, and all the dependencies will be configured during the `install` command. You can also configure the behaviour of [Acceptance testing for the Moodle App](https://moodledev.io/general/app/development/testing/acceptance-testing) using the following env variables:

- `MOODLE_APP_DOCKER_IMAGE`: Tag of [the Moodle App Docker image](https://moodledev.io/general/app/development/setup/docker-images) to use for running the app. The default value is `moodlehq/moodleapp:latest-test`.
- `MOODLE_APP_BEHAT_PLUGIN_PROJECT`: Project in github to use for installing the plugin with Behat steps specific to the Moodle App. The default value is `moodlehq/moodle-local_moodleappbehat`. This variable will be ignored if `MOODLE_APP_BEHAT_PLUGIN_REPOSITORY` is set.
- `MOODLE_APP_BEHAT_PLUGIN_REPOSITORY`: Repository url to use for installing the plugin with Behat steps specific to the Moodle App. By default, the github repository defined in `MOODLE_APP_BEHAT_PLUGIN_PROJECT` will be used.
- `MOODLE_APP_BEHAT_PLUGIN_BRANCH`: Branch of the repository to use for installing the plugin with Behat steps specific to the Moodle App. The default value is `latest`.
- `MOODLE_BEHAT_IONIC_WWWROOT`: Value to use in `$CFG->behat_ionic_wwwroot`. The default value is `http://localhost:8100`. This value should only be used if `MOODLE_APP` is not set, and the Moodle App dependencies are configured manually.

Finally, keep in mind that mobile tests only run on Chrome, so make sure that you're using the correct browser. If you're setting the `MOODLE_APP` variable, the default browser should already be Chrome; but you can set it explicitly using the `--profile` flag otherwise.

For specific examples, look at the [gha.dist.yml](GHAFileExplained.md) or [.travis.dist.yml](TravisFileExplained.md) files, and uncomment the lines mentioning the app.

**Important:** Please notice that `MOODLE_APP` only works starting with version 4.1 of the app. If you want to use this setup against older versions, you'll have to use `MOODLE_BEHAT_IONIC_WWWROOT` and configure the dependencies manually (installing the plugin, launching the docker image, etc.).
