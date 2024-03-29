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

use JakubOnderka\PhpParallelLint\Manager;
use JakubOnderka\PhpParallelLint\Settings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run PHP Lint on a plugin.
 */
class PHPLintCommand extends AbstractPluginCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('phplint')
            ->setDescription('Run PHP Lint on a plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'PHP Lint on %s');

        $files = $this->plugin->getFiles(Finder::create()->name('*.php'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        $settings = new Settings();
        $settings->addPaths($files);

        $manager = new Manager();
        try {
            $result = $manager->run($settings);
        } catch (\Exception $e) {
            return 1;
        }

        return $result->hasError() ? 1 : 0;
    }
}
