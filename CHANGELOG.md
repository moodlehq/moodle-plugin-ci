# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

The format of this change log follows the advice given at [Keep a CHANGELOG](http://keepachangelog.com).

## [Unreleased]
### Added
- Phing target _Install_ to `install.xml`.  This is the default install option.
- Phing target _ReportProperties_ to `script.xml`.  Prints build properties, helps with debugging.
- Phing target _Behat_ to `script.xml`.  Runs plugin Behat tests.
- Phing target _PHPUnit_ to `script.xml`.  Runs plugin PHPUnit tests.
- Phing target _PHPLint_ to `script.xml`.  Lints PHP files in the plugin.
- Phing target _CodeChecker_ to `script.xml`.  Run Moodle Code Checker on the plugin.
- Phing target _PHPCopyPasteDetector_ to `script.xml`.  Run PHP Copy/Paste Detector on the plugin.
- Phing target _PHPMessDetector_ to `script.xml`.  Run PHP Mess Detector on the plugin.
- Phing target _JSHint_ to `script.xml`.  Run JSHint on the Javascript files in the plugin.
- Phing target _Shifter_ to `script.xml`.  Run YUI Shifter on plugin YUI modules.
- Phing target _CSSLint_ to `script.xml`.  Lints the CSS files in the plugin.

[Unreleased]: https://github.com/mrmark/moodle-travis-plugin/commits/master