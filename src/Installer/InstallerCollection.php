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

/**
 * Installer collection.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InstallerCollection
{
    /**
     * @var AbstractInstaller[]
     */
    private $installers = [];

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var ProgressBar|null
     */
    public $progressBar;

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
    public function add(AbstractInstaller $installer)
    {
        $installer->setLogger($this->logger);
        $installer->setProgressBar($this->progressBar);

        $this->totalSteps += $installer->stepCount();

        $this->installers[] = $installer;
    }

    /**
     * @return AbstractInstaller[]
     */
    public function all()
    {
        return $this->installers;
    }

    /**
     * Merge the environment variables from all installers.
     *
     * @return array
     */
    public function mergeEnv()
    {
        $env = [];
        foreach ($this->installers as $installer) {
            $env = array_merge($env, $installer->env);
        }

        return $env;
    }

    /**
     * Get the total number of steps from all installers.
     *
     * @return int
     */
    public function totalSteps()
    {
        return $this->totalSteps;
    }
}
