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

use JakubOnderka\PhpParallelLint\Manager;
use JakubOnderka\PhpParallelLint\Settings;
use Moodlerooms\MoodlePluginCI\Bridge\MoodleConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Add a line of configuration to Moodle's config file.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class AddConfigCommand extends Command
{
    use MoodleOptionTrait;

    protected function configure()
    {
        $this->addMoodleOption($this)
            ->setName('add-config')
            ->setDescription('Add a line to the Moodle config.php file')
            ->addArgument('line', InputArgument::REQUIRED, 'Line of PHP code to add to the Moodle config.php file');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeMoodle($input);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $line = $input->getArgument('line');
        $file = $this->moodle->directory.'/config.php';

        $config   = new MoodleConfig();
        $contents = $config->read($file);
        $contents = $config->injectLine($contents, $line);
        $config->dump($file, $contents);

        $output->writeln('<info>Updated Moodle config.php file with the following line:</info>');
        $output->writeln(['', $line, '']);

        return $this->lintFile($file, $output);
    }

    /**
     * Check a single file for PHP syntax errors.
     *
     * @param string          $file   Path to the file to lint
     * @param OutputInterface $output
     *
     * @return int
     */
    public function lintFile($file, OutputInterface $output)
    {
        $manager  = new Manager();
        $settings = new Settings();
        $settings->addPaths([$file]);

        ob_start();
        $result = $manager->run($settings);
        $buffer = ob_get_contents();
        ob_end_clean();

        if ($result->hasError()) {
            $output->writeln('<error>Syntax error was found in config.php after it was updated.</error>');
            $output->writeln(['<error>Review the PHP Lint report for more details:</error>', '', $buffer]);
        }

        return $result->hasError() ? 1 : 0;
    }
}
