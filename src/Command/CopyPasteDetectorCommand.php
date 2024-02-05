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
use SebastianBergmann\Timer\ResourceUsageFormatter;
use SebastianBergmann\Timer\Timer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run PHP Copy/Paste Detector on a plugin.
 *
 * @deprecated Since 4.4.0, to be removed in 5.0.0. No replacement is planned.
 */
class CopyPasteDetectorCommand extends AbstractPluginCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('phpcpd')
            ->setDescription('Run PHP Copy/Paste Detector on a plugin (**DEPRECATED**)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!defined('PHPUNIT_TEST')) { // Only show deprecation warnings in non-test environments.
            trigger_deprecation(
                'moodle-plugin-ci',
                '4,4,0',
                'The "%s" command is deprecated and will be removed in %s. No replacement is planned.',
                $this->getName(),
                '5.0.0'
            );
            if (getenv('GITHUB_ACTIONS')) { // Only show deprecation annotations in GitHub Actions.
                echo '::warning title=Deprecated command::The phpcpd command ' .
                    'is deprecated and will be removed in 5.0.0. No replacement is planned.' . PHP_EOL;
            }
        }

        $timer = new Timer();
        $timer->start();

        $this->outputHeading($output, 'PHP Copy/Paste Detector on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.php'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }
        $detector = new Detector(new DefaultStrategy());
        $clones   = $detector->copyPasteDetection($files);

        $printer = new Text();
        $printer->printResult($clones, true);
        $output->writeln((new ResourceUsageFormatter())->resourceUsage($timer->stop()));

        return count($clones) > 0 ? 1 : 0;
    }
}
