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

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class SelfUpdateCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('selfupdate')
            ->setDescription('Updates moodle-plugin-ci')
            ->addOption('rollback', 'r', InputOption::VALUE_NONE, 'Rollback to the last version')
            ->addOption('preview', null, InputOption::VALUE_NONE, 'Update to pre-release version')
            ->addOption('any', null, InputOption::VALUE_NONE, 'Update to most recent release');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rollback  = $input->getOption('rollback');
        $stability = GithubStrategy::STABLE;

        if ($input->getOption('preview')) {
            $stability = GithubStrategy::UNSTABLE;
        }
        if ($input->getOption('any')) {
            $stability = GithubStrategy::ANY;
        }

        $strategy = new GithubStrategy();
        $strategy->setPackageName('moodlehq/moodle-plugin-ci');
        $strategy->setPharName('moodle-plugin-ci.phar');
        $strategy->setCurrentLocalVersion($this->getApplication()->getVersion());
        $strategy->setStability($stability);

        $path = $this->getBackupPath();

        $updater = new Updater(null, false);
        $updater->setStrategyObject($strategy);
        $updater->setBackupPath($path);
        $updater->setRestorePath($path);

        // Note to self: after this point, do a LITTLE as possible because after the new Phar is in place
        // we cannot load any new files, etc.
        try {
            if ($rollback) {
                if ($updater->rollback()) {
                    $output->writeln('<info>Rollback successful!</info>');
                    exit(0);
                }
                $output->writeln('<error>Rollback failed!</error>');
                exit(1);
            }

            $result = $updater->update();

            if ($result) {
                $output->writeln('');
                $output->writeln(sprintf('Updated to version <info>%s</info>', $updater->getNewVersion()));
                $output->writeln('');
                $output->writeln(sprintf('Use <info>moodle-plugin-ci selfupdate --rollback</info> to return to version <info>%s</info>', $updater->getOldVersion()));
            } else {
                $output->writeln('<comment>Already up-to-date.</comment>');
            }
            exit(0);
        } catch (\Exception $e) {
            $output->writeln('Exception: '.$e->getMessage().PHP_EOL.$e->getTraceAsString());
            exit(1);
        }
    }

    /**
     * @return string
     */
    protected function getBackupPath(): string
    {
        $directory = getenv('HOME');
        if (empty($directory) || !is_dir($directory)) {
            throw new \RuntimeException('Your $HOME enviroment variable is either not set or is not a directory');
        }
        $directory .= '/.moodle-plugin-ci';

        (new Filesystem())->mkdir($directory);

        return $directory.'/moodle-plugin-ci-old.phar';
    }
}
