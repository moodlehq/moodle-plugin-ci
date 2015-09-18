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

use Moodlerooms\MoodlePluginCI\Bridge\Moodle;
use Moodlerooms\MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Moodle trait.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait MoodleOptionTrait
{
    /**
     * @var Moodle
     */
    public $moodle;

    /**
     * Adds the 'moodle' option to a command.
     *
     * @param Command $command
     *
     * @return Command
     */
    protected function addMoodleOption(Command $command)
    {
        $moodle = getenv('MOODLE_DIR') !== false ? getenv('MOODLE_DIR') : '.';
        $command->addOption('moodle', 'm', InputOption::VALUE_REQUIRED, 'Path to Moodle', $moodle);

        return $command;
    }

    /**
     * Initialize the moodle property based on input if necessary.
     *
     * @param InputInterface $input
     */
    protected function initializeMoodle(InputInterface $input)
    {
        if (!$this->moodle) {
            $validate     = new Validate();
            $moodleDir    = realpath($validate->directory($input->getOption('moodle')));
            $this->moodle = new Moodle($moodleDir);
        }
    }
}
