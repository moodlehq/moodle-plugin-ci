# Upgrading from 1.X to 2.0

This document outlines the steps one should take when upgrading to the new major version.

## Step 1: Review new requirements

Requirements have changed, this project now requires PHP 5.6 or later and Moodle 3.2 or later.

## Step 2: Review the change log

Detailed information about what changed in version 2 can be found in the [change log](CHANGELOG.md).

## Step 3: Review Travis configuration file 

Review the updated [.travis.dist.yml](.travis.dist.yml) and update your `.travis.yml` file in your plugin.
For detailed information about the `.travis.dist.yml` file, please see this [help document](doc/TravisFileExplained.md).