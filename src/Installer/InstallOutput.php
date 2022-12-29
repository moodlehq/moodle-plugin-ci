<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2018 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Installer;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Handles output for installation process.
 */
class InstallOutput
{
    private ?ProgressBar $progressBar = null;
    private ?LoggerInterface $logger  = null;

    /**
     * Number of steps taken.
     */
    private int $stepCount = 0;

    public function __construct(?LoggerInterface $logger = null, ?ProgressBar $progressBar = null)
    {
        $this->progressBar = $progressBar;

        // Ignore logger completely when we have a progress bar.
        if (!$this->progressBar instanceof ProgressBar) {
            $this->logger = $logger;
        }
    }

    /**
     * Get the number of steps taken.
     *
     * @return int
     */
    public function getStepCount(): int
    {
        return $this->stepCount;
    }

    /**
     * Starting the installation process.
     *
     * @param string $message  Start message
     * @param int    $maxSteps The number of steps that will be taken
     */
    public function start(string $message, int $maxSteps): void
    {
        $this->info($message);

        if ($this->progressBar instanceof ProgressBar) {
            $this->progressBar->setMessage($message);
            $this->progressBar->start($maxSteps);
        }
    }

    /**
     * Signify the move to the next step in the installation.
     *
     * @param string $message Very short message about the step
     */
    public function step(string $message): void
    {
        ++$this->stepCount;

        $this->info($message);

        if ($this->progressBar instanceof ProgressBar) {
            $this->progressBar->setMessage($message);
            $this->progressBar->advance();
        }
    }

    /**
     * Ending the installation process.
     *
     * @param string $message End message
     */
    public function end(string $message): void
    {
        $this->info($message);

        if ($this->progressBar instanceof ProgressBar) {
            $this->progressBar->setMessage($message);
            $this->progressBar->finish();
        }
    }

    /**
     * Log a message, shown in lower verbosity mode.
     *
     * @param string $message
     * @param array  $context
     */
    public function info(string $message, array $context = []): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * Log a message, shown in the highest verbosity mode.
     *
     * @param string $message
     * @param array  $context
     */
    public function debug(string $message, array $context = []): void
    {
        if ($this->logger instanceof LoggerInterface) {
            $this->logger->debug($message, $context);
        }
    }
}
