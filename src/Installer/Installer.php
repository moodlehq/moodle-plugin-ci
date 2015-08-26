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

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Installer
{
    /**
     * @var AbstractInstaller[]
     */
    private $installers = [];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    /**
     * Total number of steps from all the added installers.
     *
     * @var int
     */
    private $totalSteps = 0;

    /**
     * @param LoggerInterface  $logger
     * @param ProgressBar|null $progressBar
     */
    public function __construct(LoggerInterface $logger, ProgressBar $progressBar = null)
    {
        $this->logger      = $logger;
        $this->progressBar = $progressBar;
    }

    /**
     * Add an installer.
     *
     * @param AbstractInstaller $installer
     */
    public function addInstaller(AbstractInstaller $installer)
    {
        $installer->setLogger($this->logger);
        $installer->setProgressBar($this->progressBar);

        $this->totalSteps += $installer->stepCount();

        $this->installers[] = $installer;
    }

    /**
     * Run the entire install process.
     */
    public function runInstallation()
    {
        $this->logger->info('Starting install');

        if ($this->progressBar instanceof ProgressBar) {
            $this->progressBar->setMessage('Starting install');
            $this->progressBar->start($this->totalSteps + 1);
        }

        foreach ($this->installers as $installer) {
            $installer->install();
        }
        $this->writeEnvFile();

        $this->logger->info('Install completed');

        if ($this->progressBar instanceof ProgressBar) {
            $this->progressBar->setMessage('Install completed');
            $this->progressBar->finish();
        }
    }

    /**
     * After installers run, write their environment variables to a file.
     */
    public function writeEnvFile()
    {
        $variables = [];
        foreach ($this->installers as $installer) {
            $variables = array_merge($variables, $installer->env);
        }
        $content = '';
        foreach ($variables as $name => $value) {
            $content .= sprintf('%s=%s', $name, $value).PHP_EOL;
        }

        $filesystem = new Filesystem();
        $filesystem->dumpFile('~/.env-travis', $content);
    }
}
