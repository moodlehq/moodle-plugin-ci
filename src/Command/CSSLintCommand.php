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
 * Run CSS Lint on a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CSSLintCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('csslint')
            ->setDescription('Run CSS Lint on a plugin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'CSS Lint on %s');

        $ignoreRules = 'adjoining-classes,box-sizing,box-model,overqualified-elements,bulletproof-font-face,'.
            'compatible-vendor-prefixes,selector-max-approaching,fallback-colors,floats,ids,'.
            'qualified-headings,selector-max,unique-headings';

        $files = $this->plugin->getRelativeFiles(Finder::create()->name('*.css')->notName('*-min.css'));
        if (count($files) === 0) {
            return $this->outputSkip($output);
        }
        $process = $this->execute->passThrough(sprintf('csslint --ignore=%s %s', $ignoreRules, implode(' ', $files)), $this->plugin->directory);

        return $process->isSuccessful() ? 0 : 1;
    }
}
