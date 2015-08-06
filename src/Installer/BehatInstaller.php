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
use Moodlerooms\MoodlePluginCI\Process\Execute;
use Symfony\Component\Process\Process;

/**
 * Behat installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class BehatInstaller extends AbstractInstaller
{
    /**
     * @var Moodle
     */
    private $moodle;

    /**
     * @var Execute
     */
    private $execute;

    public function __construct(Moodle $moodle, Execute $execute)
    {
        $this->moodle  = $moodle;
        $this->execute = $execute;
    }

    public function install()
    {
        $this->step('Download Selenium');

        $jar     = $this->moodle->directory.'/selenium.jar';
        $process = new Process("curl -o $jar http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar");
        $process->setTimeout(120);

        $this->execute->mustRun($process);

        $this->step('Starting servers');

        $binDir = realpath(__DIR__.'/../../bin');

        $this->log('Starting Selenium server');
        $this->execute->mustRun("$binDir/start_selenium $jar");

        $this->log('Starting PhantomJS');
        $this->execute->mustRun("$binDir/start_phantomjs");

        $this->log('Starting PHP server');
        $this->execute->mustRun(new Process("$binDir/start_phpserver", $this->moodle->directory));

        // Moodle 2.9 or later use this one.
        $behatUtility = $this->moodle->directory.'/admin/tool/behat/cli/util_single_run.php';
        if (!file_exists($behatUtility)) {
            // Moodle 2.8 or earlier use this one.
            $behatUtility = $this->moodle->directory.'/admin/tool/behat/cli/util.php';
        }

        $this->step('Initialize Behat');
        $process = new Process("php $behatUtility --install");
        $process->setTimeout(null);

        // TODO: Grep output for debugging or PHP notices/errors.

        $this->execute->mustRun($process);

        $this->step('Enabling Behat');
        $process = new Process("php $behatUtility --enable");
        $process->setTimeout(null);

        $this->execute->mustRun($process);
    }

    public function stepCount()
    {
        return 4;
    }
}
