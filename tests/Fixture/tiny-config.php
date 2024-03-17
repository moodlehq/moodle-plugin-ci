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
$CFG->dboptions = [
    'dbport' => '',
];

$CFG->wwwroot  = 'http://localhost/moodle';
$CFG->dataroot = '/path/to/moodledata';
$CFG->admin    = 'admin';

$CFG->directorypermissions = 02777;

// Show debugging messages.
$CFG->debug        = (E_ALL | E_STRICT);
$CFG->debugdisplay = 1;

// Extra config.
$CFG->exists = 'exists';

// require_once(__DIR__.'/lib/setup.php');
// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
