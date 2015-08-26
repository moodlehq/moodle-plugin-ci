<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Installer;

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
use Moodlerooms\MoodlePluginCI\Installer\Database\AbstractDatabase;
use Moodlerooms\MoodlePluginCI\Process\Execute;
use Moodlerooms\MoodlePluginCI\Validate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleInstaller extends AbstractInstaller
{
    /**
     * @var Execute
     */
    private $execute;

    /**
     * @var AbstractDatabase
     */
    private $database;

    /**
     * @var Moodle
     */
    private $moodle;

    /**
     * @var string
     */
    private $branch;

    /**
     * @var string
     */
    private $dataDir;

    /**
     * @param Execute          $execute
     * @param AbstractDatabase $database
     * @param Moodle           $moodle
     * @param string           $branch
     * @param string           $dataDir
     */
    public function __construct(Execute $execute, AbstractDatabase $database, Moodle $moodle, $branch, $dataDir)
    {
        $this->execute  = $execute;
        $this->database = $database;
        $this->moodle   = $moodle;
        $this->branch   = $branch;
        $this->dataDir  = $dataDir;
    }

    public function install()
    {
        $this->step('Cloning Moodle');

        $process = new Process(sprintf('git clone --depth=1 --branch %s git://github.com/moodle/moodle %s', $this->branch, $this->moodle->directory));
        $process->setTimeout(null);
        $this->execute->mustRun($process);

        // Expand the path to Moodle so all other installers use absolute path.
        $this->moodle->directory = $this->expandPath($this->moodle->directory);

        $this->step('Moodle assets');

        $this->log('Creating Moodle data directories');
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->dataDir);
        $filesystem->mkdir($this->dataDir.'/phpu_moodledata');
        $filesystem->mkdir($this->dataDir.'/behat_moodledata');

        $this->log('Create Moodle database');
        $this->execute->mustRun($this->database->getCreateDatabaseCommand());

        $this->log('Creating Moodle\'s config file');
        $filesystem->dumpFile($this->moodle->directory.'/config.php', $this->generateConfig($this->expandPath($this->dataDir)));

        $this->step('Clone Code Checker');

        $this->execute->mustRun(
            sprintf('git clone --depth=1 --branch master git://github.com/moodlehq/moodle-local_codechecker.git %s', $this->moodle->directory.'/local/codechecker')
        );

        $this->addEnv('MOODLE_DIR', $this->moodle->directory);
    }

    /**
     * Create a Moodle config.
     *
     * @param string $dataDir
     *
     * @return string
     */
    public function generateConfig($dataDir)
    {
        $template  = file_get_contents(__DIR__.'/../../res/template/config.php.txt');
        $variables = [
            '{{DBTYPE}}'          => $this->database->type,
            '{{DBLIBRARY}}'       => $this->database->library,
            '{{DBHOST}}'          => $this->database->host,
            '{{DBNAME}}'          => $this->database->name,
            '{{DBUSER}}'          => $this->database->user,
            '{{DBPASS}}'          => $this->database->pass,
            '{{WWWROOT}}'         => 'http://localhost/moodle',
            '{{DATAROOT}}'        => $dataDir,
            '{{PHPUNITDATAROOT}}' => $dataDir.'/phpu_moodledata',
            '{{BEHATDATAROOT}}'   => $dataDir.'/behat_moodledata',
            '{{BEHATWWWROOT}}'    => 'http://localhost:8000',
        ];

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Converts a path to an absolute path.
     *
     * @param string $path
     *
     * @return string
     */
    public function expandPath($path)
    {
        $validate = new Validate();

        return realpath($validate->directory($path));
    }

    public function stepCount()
    {
        return 3;
    }
}
