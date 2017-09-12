<?php // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'localhost';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'root';
$CFG->dbpass    = '';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = [];

$CFG->wwwroot  = 'http://localhost/moodle';
$CFG->dataroot = '/path/to/moodledata';
$CFG->admin    = 'admin';

$CFG->directorypermissions = 02777;

// Show debugging messages.
$CFG->debug        = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;

// No emails.
$CFG->noemailever    = true;
$CFG->noreplyaddress = 'noreply@localhost.local';

// PHPUnit settings.
$CFG->phpunit_prefix   = 'phpu_';
$CFG->phpunit_dataroot = '/path/to/moodledata/phpu_moodledata';

// Behat settings.
$CFG->behat_prefix        = 'behat_';
$CFG->behat_dataroot      = '/path/to/moodledata/behat_moodledata';
$CFG->behat_wwwroot       = 'http://localhost:8000';
$CFG->behat_faildump_path = '/path/to/moodledata/behat_dump';
$CFG->behat_profiles      = [
    'default' => [
        'browser' => 'firefox',
        'wd_host' => 'http://localhost:4444/wd/hub',
    ],
    'chrome' => [
        'browser' => 'chrome',
        'wd_host' => 'http://localhost:4444/wd/hub',
    ],
];

// Extra config.

require_once(__DIR__.'/lib/setup.php');
// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
