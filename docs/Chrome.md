---
layout: page
title: Using Chrome with Behat
---

First, a word of warning.  On Travis CI, you cannot specify the version of Chrome to install
which might lead to compatibility problems as Travis CI updates their environment. So,
the most _stable_ route is to keep using Firefox.  Currently Chrome is working with the `Trusy`
Travis CI environment and the versions as of writing this are:

* ChromeDriver 2.29
* Chromium 60

So, why would you want to use Chrome then?  Speed.  It is much faster than Firefox for executing
Behat tests.  If you find that you are waiting too long for your build results or if you find
that your build exceeds max execution time, then using Chrome with Behat could help.  To use
Chrome, modify your `.travis.yml` file.

Under `addons` add `chromium-chromedriver`:

```yaml
addons:
  # ...other stuff.
  apt:
    packages:
      # ...other stuff.
      - chromium-chromedriver
```

The under `script` update your Behat command:

```yaml
script:
  # ...other stuff.
  - moodle-plugin-ci behat --profile chrome
```

## Troubleshooting

If Behat starts to misbehave, please revert back to using Firefox to see if it is an issue with
using Chrome or not.

If you ever need to see the version of Chrome or ChromeDriver in your build, then you can add these
to your `.travis.yml` file:

```yaml
install:
  - /usr/lib/chromium-browser/chromedriver --version
  - chromium-browser --version
  # ...other stuff.
```