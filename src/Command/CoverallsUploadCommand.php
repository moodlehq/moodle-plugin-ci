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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Upload code coverage to Coveralls.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CoverallsUploadCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('coveralls-upload')
            ->setDescription('Upload code coverage to Coveralls')
            ->addOption('coverage-file', null, InputOption::VALUE_REQUIRED, 'Location of the Clover XML file to upload', './coverage.xml');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $coverage = realpath($input->getOption('coverage-file'));
        if ($coverage === false) {
            $output->writeln(sprintf('Did not find coverage file at <info>%s</info>', $input->getOption('coverage-file')));

            return 0;
        }

        $process = new Process('composer create-project -n --no-dev --prefer-dist satooshi/php-coveralls _php_coveralls ^1', $this->plugin->directory);
        $this->execute->mustRun($process);

        $filesystem = new Filesystem();
        $filesystem->copy($coverage, $this->plugin->directory.'/build/logs/clover.xml');

        $process = $this->execute->passThrough('_php_coveralls/bin/coveralls -v', $this->plugin->directory);

        return $process->isSuccessful() ? 0 : 1;
    }
}
