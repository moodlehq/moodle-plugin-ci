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

namespace Moodlerooms\MoodlePluginCI\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run JSHint on a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class JSHintCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('jshint')
            ->setDescription('Run JSHint on a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'JSHint on %s');

        $files = $this->plugin->getRelativeFiles(
            Finder::create()->name('*.js')->notName('*-min.js')->notPath('yui/build')->notPath('amd/build')
        );

        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        $process = $this->execute->passThrough('jshint '.implode(' ', $files), $this->plugin->directory);

        return $process->isSuccessful() ? 0 : 1;
    }
}
