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
use Psr\Log\LogLevel;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Abstract Installer.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class AbstractInstaller
{
    /**
     * @var ProgressBar|null
     */
    private $progressBar;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Environment variables to write out.
     *
     * @var array
     */
    public $env = [];

    /**
     * Actual number of steps after running.
     *
     * @var int
     */
    private $actualSteps = 0;

    /**
     * @param ProgressBar|null $progressBar
     */
    public function setProgressBar(ProgressBar $progressBar = null)
    {
        $this->progressBar = $progressBar;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Run install.
     */
    abstract public function install();

    /**
     * Get the number of steps this installer will perform.
     *
     * @return int
     */
    abstract public function stepCount();

    /**
     * Get the actual number of steps taken during install.
     *
     * @return int
     */
    public function actualStepCount()
    {
        return $this->actualSteps;
    }

    /**
     * Signify the move to the next step in the install.
     *
     * @param string $message Very short message about the step
     */
    public function step($message)
    {
        ++$this->actualSteps;

        $this->log($message, LogLevel::INFO);

        if ($this->progressBar instanceof ProgressBar) {
            $this->progressBar->setMessage($message);
            $this->progressBar->advance();
        }
    }

    /**
     * Log a message.
     *
     * @param string $message
     * @param string $level
     * @param array  $context
     */
    public function log($message, $level = LogLevel::DEBUG, array $context = [])
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Add a variable to write to the environment.
     *
     * @param string $name
     * @param string $value
     */
    public function addEnv($name, $value)
    {
        $this->env[$name] = $value;
    }
}
