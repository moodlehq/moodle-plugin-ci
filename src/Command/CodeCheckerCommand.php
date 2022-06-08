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

use MoodlePluginCI\StandardResolver;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Reporter;
use PHP_CodeSniffer\Runner;
use PHP_CodeSniffer\Util\Timing;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

// Cannot be autoload from composer, because this autoloader is used for both
// phpcs and any standard Sniff, so it must be loaded at the end. For more info, see:
// https://github.com/squizlabs/PHP_CodeSniffer/issues/1463#issuecomment-300637855
//
// The alternative is to, instead of using PHP_CodeSniffer like a library, just
// use the binaries bundled with it (phpcs, phpcbf...) but we want to use as lib for now.
require_once __DIR__.'/../../vendor/squizlabs/php_codesniffer/autoload.php';

/**
 * Run Moodle Code Checker on a plugin.
 */
class CodeCheckerCommand extends AbstractPluginCommand
{
    /**
     * The coding standard to use.
     *
     * @var string
     */
    protected $standard;

    /**
     * Used to find the files to process.
     *
     * @var Finder
     */
    protected $finder;

    protected function configure()
    {
        parent::configure();

        $this->setName('codechecker')
            ->setDescription('Run Moodle Code Checker on a plugin')
            ->addOption('standard', 's', InputOption::VALUE_REQUIRED, 'The name or path of the coding standard to use', 'moodle')
            ->addOption('max-warnings', null, InputOption::VALUE_REQUIRED,
                'Number of warnings to trigger nonzero exit code - default: -1', -1);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        // Resolve the coding standard.
        $resolver       = new StandardResolver();
        $this->standard = $input->getOption('standard');
        if ($resolver->hasStandard($this->standard)) {
            $this->standard = $resolver->resolve($this->standard);
        }

        $this->finder = Finder::create()->name('*.php');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Moodle Code Checker on %s');

        $files = $this->plugin->getFiles($this->finder);
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }

        // Needed constant.
        if (defined('PHP_CODESNIFFER_CBF') === false) {
            define('PHP_CODESNIFFER_CBF', false);
        }

        Timing::startTiming();

        $runner                    = new Runner();
        $runner->config            = new Config(['--parallel=1']); // Pass a param to shortcut params coming from caller CLI.

        // This is not needed normally, because phpcs loads the CodeSniffer.conf from its
        // root directory. But when this is run from within a .phar file, it expects the
        // config file to be out from the phar, in the same directory.
        //
        // While this approach is logic and enabled to configure phpcs, we don't want that
        // in this case, we just want to ensure phpcs knows about our standards,
        // so we are going to force the installed_paths config here.
        //
        // And it needs need to do it BEFORE the runner init! (or it's lost)
        //
        // Note: the "moodle" one is not really needed, because it's autodetected normally,
        // but the PHPCompatibility one is. There are some issues about version PHPCompatibility 10
        // to stop requiring to be configured here, but that's future version. Revisit this when
        // available.
        //
        // Note: the paths are relative to the base CodeSniffer directory, aka, the directory
        // where "src" sits.
        $runner->config->setConfigData('installed_paths', './../../phpcompatibility/php-compatibility/PHPCompatibility');
        $runner->config->standards = [$this->standard]; // Also BEFORE init() or it's lost.

        $runner->init();

        $runner->config->parallel     = 1;
        $runner->config->reports      = ['full' => null];
        $runner->config->colors       = $output->isDecorated();
        $runner->config->verbosity    = 0;
        $runner->config->encoding     = 'utf-8';
        $runner->config->showProgress = true;
        $runner->config->showSources  = true;
        $runner->config->interactive  = false;
        $runner->config->cache        = false;
        $runner->config->extensions   = ['php' => 'PHP'];
        $runner->config->reportWidth  = 132;

        $runner->config->files = $files;

        $runner->config->setConfigData('testVersion', PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION, true);

        // Create the reporter to manage all the reports from the run.
        $runner->reporter = new Reporter($runner->config);

        // And build the file list to iterate over.
        /** @var object[] $todo */
        $todo         = new \PHP_CodeSniffer\Files\FileList($runner->config, $runner->ruleset);
        $numFiles     = count($todo);
        $numProcessed = 0;

        foreach ($todo as $file) {
            if ($file->ignored === false) {
                try {
                    $runner->processFile($file);
                    ++$numProcessed;
                    $runner->printProgress($file, $numFiles, $numProcessed);
                } catch (\PHP_CodeSniffer\Exceptions\DeepExitException $e) {
                    echo $e->getMessage();

                    return $e->getCode();
                } catch (\Exception $e) {
                    $error = 'Problem during processing; checking has been aborted. The error message was: '.$e->getMessage();
                    $file->addErrorOnLine($error, 1, 'Internal.Exception');
                }
                $file->cleanUp();
            }
        }

        // Have finished, generate the final reports.
        $runner->reporter->printReports();

        $maxwarnings = (int) $input->getOption('max-warnings');

        return ($runner->reporter->totalErrors > 0 || ($maxwarnings >= 0 && $runner->reporter->totalWarnings > $maxwarnings)) ? 1 : 0;
    }
}
