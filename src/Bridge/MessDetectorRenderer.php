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

namespace MoodlePluginCI\Bridge;

use PHPMD\AbstractRenderer;
use PHPMD\ProcessingError;
use PHPMD\Report;
use PHPMD\RuleViolation;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Custom Mess Detector report.
 */
class MessDetectorRenderer extends AbstractRenderer
{
    protected OutputInterface $output;
    private string $basePath;

    /**
     * @param OutputInterface $output
     * @param string          $basePath
     */
    public function __construct(OutputInterface $output, string $basePath)
    {
        $this->output   = $output;
        $this->basePath = $basePath;
    }

    public function renderReport(Report $report): void
    {
        $this->output->writeln('');

        $groupByFile = [];
        foreach ($report->getRuleViolations() as $violation) {
            if ($filename = $violation->getFileName()) {
                $groupByFile[$filename][] = $violation;
            }
        }

        foreach ($report->getErrors() as $error) {
            $groupByFile[$error->getFile()][] = $error;
        }
        foreach ($groupByFile as $file => $problems) {
            $violationCount = 0;
            $errorCount     = 0;

            $table = new Table($this->output);
            $table->setStyle('borderless');
            foreach ($problems as $problem) {
                if ($problem instanceof RuleViolation) {
                    $table->addRow([$problem->getBeginLine(), '<comment>VIOLATION</comment>', $problem->getDescription()]);
                    ++$violationCount;
                }
                if ($problem instanceof ProcessingError) {
                    $table->addRow(['-', '<error>ERROR</error>', $problem->getMessage()]);
                    ++$errorCount;
                }
            }

            $this->output->writeln([
                sprintf('<fg=white;options=bold>FILE: %s</>', str_replace($this->basePath.'/', '', $file)),
                sprintf('<fg=white;options=bold>FOUND %d ERRORS AND %d VIOLATIONS</>', $errorCount, $violationCount),
            ]);
            $table->render();
            $this->output->writeln('');
        }
    }
}
