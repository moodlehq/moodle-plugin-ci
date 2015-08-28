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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract Moodle Command.
 *
 * This command interacts with Moodle and a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class AbstractMoodleCommand extends AbstractPluginCommand
{
    /**
     * @var Moodle
     */
    public $moodle;

    protected function configure()
    {
        parent::configure();

        $moodle = getenv('MOODLE_DIR') !== false ? getenv('MOODLE_DIR') : '.';
        $this->addOption('moodle', 'm', InputOption::VALUE_OPTIONAL, 'Path to Moodle', $moodle);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        if (!$this->moodle) {
            $validate     = new Validate();
            $moodleDir    = realpath($validate->directory($input->getOption('moodle')));
            $this->moodle = new Moodle($moodleDir);
        }
    }
}
