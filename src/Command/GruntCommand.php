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

use MoodlePluginCI\Model\GruntTaskModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Run grunt tasks.
 */
class GruntCommand extends AbstractMoodleCommand
{
    use ExecuteTrait;

    /**
     * @var string
     */
    public $backupDir;

    protected function configure()
    {
        parent::configure();

        $tasks = ['amd', 'yui', 'gherkinlint', 'stylelint:css', 'stylelint:less', 'stylelint:scss'];

        $this->setName('grunt')
            ->setDescription('Run Grunt task on a plugin')
            ->addOption('tasks', 't', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The Grunt tasks to run', $tasks)
            ->addOption('show-lint-warnings', null, InputOption::VALUE_NONE, 'Show eslint warnings')
            ->addOption('max-lint-warnings', null, InputOption::VALUE_REQUIRED,
            'Maximum number of eslint warnings', '');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
        $this->backupDir = $this->backupDir ?: sys_get_temp_dir().'/moodle-plugin-ci-grunt-backup-'.time();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Grunt on %s');
        $this->backupPlugin();

        $code  = 0;
        $files = new Filesystem();
        $tasks = $input->getOption('tasks');

        foreach ($tasks as $taskName) {
            $task = $this->toGruntTask($taskName);
            if ($task === null) {
                continue; // Means plugin lacks requirements or Moodle does.
            }

            $builder = ProcessBuilder::create()
                ->setPrefix('grunt');
            if ($input->getOption('show-lint-warnings')) {
                $builder->add('--show-lint-warnings');
            }
            if (strlen($input->getOption('max-lint-warnings'))) {
                $builder->add('--max-lint-warnings='.((int) $input->getOption('max-lint-warnings')));
            }
            $builder
                ->add($task->taskName)
                ->setWorkingDirectory($task->workingDirectory)
                ->setTimeout(null);

            // Remove build directory so we can detect files that should be deleted.
            if (!empty($task->buildDirectory)) {
                $files->remove($this->plugin->directory.'/'.$task->buildDirectory);
            }

            $process = $this->execute->passThroughProcess($builder->getProcess());
            if (!$process->isSuccessful()) {
                $code = 1;
            }
        }

        if ($code === 0) {
            $code = $this->validatePluginFiles($output);
        }

        $this->restorePlugin();
        (new Filesystem())->remove($this->backupDir);

        return $code;
    }

    /**
     * Backup the plugin so we can use it for comparison and restores.
     */
    public function backupPlugin()
    {
        (new Filesystem())->mirror($this->plugin->directory, $this->backupDir);
    }

    /**
     * Revert any changes Grunt tasks might have done.
     */
    public function restorePlugin()
    {
        (new Filesystem())->mirror($this->backupDir, $this->plugin->directory, null, ['delete' => true, 'override' => true]);
    }

    /**
     * Verify that no plugin files were modified, need to be deleted or were added.
     *
     * Only checks JS and CSS files.
     *
     * @return int
     */
    public function validatePluginFiles(OutputInterface $output)
    {
        $code = 0;

        // Look for modified files or files that should be deleted.
        $files = Finder::create()->files()->in($this->backupDir)->name('*.js')->name('*.css')->getIterator();
        foreach ($files as $file) {
            $compareFile = $this->plugin->directory.'/'.$file->getRelativePathname();
            if (!file_exists($compareFile)) {
                $output->writeln(sprintf('<error>File no longer generated and likely should be deleted: %s</error>', $file->getRelativePathname()));
                $code = 1;
                continue;
            }

            if (sha1_file($file->getPathname()) !== sha1_file($compareFile)) {
                $output->writeln(sprintf('<error>File is stale and needs to be rebuilt: %s</error>', $file->getRelativePathname()));
                $code = 1;
            }
        }

        // Look for newly generated files.
        $files = Finder::create()->files()->in($this->plugin->directory)->name('*.js')->name('*.css')->getIterator();
        foreach ($files as $file) {
            if (!file_exists($this->backupDir.'/'.$file->getRelativePathname())) {
                $output->writeln(sprintf('<error>File is newly generated and needs to be added: %s</error>', $file->getRelativePathname()));
                $code = 1;
            }
        }

        return $code;
    }

    /**
     * Create a Grunt Task Model based on the task we are trying to run.
     *
     * @param string $task
     *
     * @return GruntTaskModel|null
     */
    public function toGruntTask($task)
    {
        $workingDirectory = $this->moodle->directory;
        if (is_file($this->plugin->directory.'/Gruntfile.js')) {
            $workingDirectory = $this->plugin->directory;
        }
        $defaultTask = new GruntTaskModel($task, $workingDirectory);

        switch ($task) {
            case 'amd':
                $amdDir = $this->plugin->directory.'/amd';
                if (!is_dir($amdDir)) {
                    return null;
                }

                return new GruntTaskModel($task, $amdDir, 'amd/build');
            case 'shifter':
            case 'yui':
                $yuiDir = $this->plugin->directory.'/yui';
                if (!is_dir($yuiDir)) {
                    return null;
                }

                return new GruntTaskModel($task, $yuiDir.'/src', 'yui/build');
            case 'gherkinlint':
                if ($this->moodle->getBranch() < 33 || !$this->plugin->hasBehatFeatures()) {
                    return null;
                }

                return new GruntTaskModel($task, $this->moodle->directory);
            case 'stylelint:css':
                return $this->plugin->hasFilesWithName('*.css') ? $defaultTask : null;
            case 'stylelint:less':
                return $this->plugin->hasFilesWithName('*.less') ? $defaultTask : null;
            case 'stylelint:scss':
                return $this->plugin->hasFilesWithName('*.scss') ? $defaultTask : null;
            default:
                return $defaultTask;
        }
    }
}
