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

namespace Moodlerooms\MoodlePluginCI\Bridge;

use PHPMD\AbstractRenderer;
use PHPMD\ProcessingError;
use PHPMD\Report;
use PHPMD\RuleViolation;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Custom Mess Detector report.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MessDetectorRenderer extends AbstractRenderer
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    private $basePath;

    public function __construct(OutputInterface $output, $basePath)
    {
        $this->output   = $output;
        $this->basePath = $basePath;
    }

    /**
     * This method will be called when the engine has finished the source analysis
     * phase.
     *
     * @param \PHPMD\Report $report
     */
    public function renderReport(Report $report)
    {
        $this->output->writeln('');

        $groupByFile = [];
        /** @var RuleViolation $violation */
        foreach ($report->getRuleViolations() as $violation) {
            $groupByFile[$violation->getFileName()][] = $violation;
        }

        /** @var ProcessingError $error */
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
