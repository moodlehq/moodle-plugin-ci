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

namespace MoodlePluginCI\Command;

use SebastianBergmann\PHPCPD\Detector\Detector;
use SebastianBergmann\PHPCPD\Detector\Strategy\DefaultStrategy;
use SebastianBergmann\PHPCPD\Log\Text;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run PHP Copy/Paste Detector on a plugin.
 */
class CopyPasteDetectorCommand extends AbstractPluginCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('phpcpd')
            ->setDescription('Run PHP Copy/Paste Detector on a plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'PHP Copy/Paste Detector on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.php'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }
        $detector = new Detector(new DefaultStrategy());
        $clones   = $detector->copyPasteDetection($files);

        $printer = new Text();
        $printer->printResult($output, $clones);
        $output->writeln(Timer::resourceUsage());

        return count($clones) > 0 ? 1 : 0;
    }
}
