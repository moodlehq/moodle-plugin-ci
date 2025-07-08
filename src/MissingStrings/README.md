# Missing Strings Validation for Moodle Plugin CI

## Overview

Validates language strings in Moodle plugins to ensure all required strings are defined and properly referenced. Detects missing strings from PHP code, JavaScript, templates, database files, and class implementations. Automatically includes subplugin validation for comprehensive coverage.

## What It Checks

### Code Usage
- `get_string()` and `new lang_string()` calls in PHP
- JavaScript string methods (`str.get_string()`, `str.get_strings()`, `getString()`, `getStrings()`, `Prefetch` methods)
- Mustache template strings (`{{#str}}`, `{{#cleanstr}}`)
- Help button strings (`->addHelpButton()`)
- Dynamic strings automatically filtered (variables like `$row->state` are ignored)

### Plugin Requirements
- **All plugins**: `pluginname`
- **Activity modules**: `modulename`, `modulenameplural`
- **Database files**: capabilities, caches, messages, tags, mobile addons, subplugins
- **Class implementations**: Privacy providers, search areas, grade items, exceptions

### Subplugin Support
- **Automatic discovery**: Reads `db/subplugins.json` and `db/subplugins.php`
- **Recursive validation**: Validates main plugin + all discovered subplugins

## Usage

```bash
# Basic validation
moodle-plugin-ci missingstrings /path/to/plugin

# Strict mode (warnings as errors)
moodle-plugin-ci missingstrings --strict /path/to/plugin

# Check for unused strings
moodle-plugin-ci missingstrings --unused /path/to/plugin

# Exclude specific string patterns
moodle-plugin-ci missingstrings --exclude-patterns="test_*,debug_*" /path/to/plugin

# Combined options
moodle-plugin-ci missingstrings --strict --unused --exclude-patterns="temp_*" /path/to/plugin
```

## Options

- `--lang=LANG`: Language to validate (default: en)
- `--strict`: Treat warnings as errors
- `--unused`: Report unused strings as warnings
- `--exclude-patterns=PATTERNS`: Comma-separated exclusion patterns (supports wildcards)
- `--debug`: Enable debug mode for detailed information

## Output

```bash
  RUN  Checking for missing language strings in mod/quiz

✗ Missing required string (string_key: pluginname, component: mod_quiz,
  file: mod/quiz/version.php, line: 28)
✗ Missing used string (string_key: error_invalid_data, component: quizaccess_timelimit,
  file: mod/quiz/accessrule/timelimit/classes/output/renderer.php, line: 134)
⚠ Unused string (defined but not used) (string_key: old_feature, component: mod_quiz)

Subplugins validated:
- quiz_grading
- quiz_overview
- quiz_responses
- quiz_statistics
- quizaccess_delaybetweenattempts
- quizaccess_ipaddress
- quizaccess_numattempts
- quizaccess_openclosedate
- quizaccess_password
- quizaccess_securewindow
- quizaccess_timelimit
- quizaccess_seb

Summary:
- Main plugin: mod_quiz (1 plugin)
- Subplugins: 12 plugins discovered and validated
- Total plugins validated: 13
- Errors: 2
- Warnings: 1

✗ Language string validation failed
```

The tool provides:
- **Component identification**: Shows which specific plugin has issues
- **Full file paths**: Relative paths from Moodle root for easy location
- **Line numbers**: Exact location of string usage
- **Subplugin coverage**: Automatic discovery and validation
- **Summary statistics**: Clear breakdown of all validation results

## Common Issues

**"Missing required string" for existing strings:**
- Check string key spelling (including colons and underscores)
- For modules, strings go in `lang/en/{modulename}.php`, not `lang/en/{component}.php`
- For subplugins, check that the language file uses the correct component name

**"Unused string" warnings:**
- Use `--exclude-patterns` to exclude test/debug strings
- Consider if the string is actually needed

**Dynamic string false positives:**
- Strings with variables (e.g., `"studentattempt:{$row->state}"`) are automatically ignored
- If legitimate dynamic strings are incorrectly filtered, use static string alternatives

**Subplugin validation:**
- Ensure `db/subplugins.json` or `db/subplugins.php` is correctly formatted
- Subplugin directories must contain `version.php`, language files
- Each subplugin is validated independently with its own component context