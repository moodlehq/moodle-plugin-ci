---
layout: page
title: Version checks
---

The `version` command checks a plugin's `version.php` without needing a Moodle install. It validates the documented properties, and when a baseline is given, it checks that the plugin version increases according to a version policy.

The command looks up the Moodle branch catalogue from the Moodle updates API, so it needs internet access.

## Basic check

Run the command against the plugin directory:

```bash
moodle-plugin-ci version plugin
```

This checks properties such as `$plugin->version` (YYYYMMDDXX form, not in the future), `$plugin->component`, `$plugin->requires`, `$plugin->supported`, `$plugin->incompatible` and `$plugin->maturity`.

## Checking the version increase

To check that the version increases, pass the base branch's `version.php` with `--baseline-dir` and choose a `--version-policy`. Both options must be given together.

* `latest`: for the branch that carries the newest version. The version must increase. Moving to a new date is recommended. Multiple changes on the same day can increment the last 2 digits.
* `frozen`: for older maintained branches. The date must stay the same, and only the last two digits may increase. This stops frozen branches overtaking the latest branch's version number.

## Example

Suppose you maintain a branch that supports Moodle 4.5+ but then make a new branch based off this for Moodle 5.3+. It is recommended to apply the `latest` version policy to the Moodle 5.3 supporting branch, and the `frozen` version policy to the Moodle 4.5 branch.

By doing this, it stops the older branch overtaking the newer branch - this means users can upgrade to the newer branch without any downgrade errors.

It would pass when:
1. **The next release on the newest branch.** You release a new feature on a new date. With `latest`, the check passes.
2. **A backport to the frozen branch.** You apply the same fix to the older branch and bump only the last two digits. With `frozen`, the check passes. If you change the date instead, `frozen` fails.

It would fail when:
1. **A forgotten bump.** Someone changes the code but leaves the version alone. With `latest`, the check fails because the version must increase.
2. **A date change on the frozen branch.** Someone moves the date forward on the older branch. With `frozen`, the check fails because the date must stay the same.
