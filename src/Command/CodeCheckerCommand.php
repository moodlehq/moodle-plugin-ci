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

use Moodlerooms\MoodlePluginCI\Bridge\CodeSnifferCLI;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Run Moodle Code Checker on a plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CodeCheckerCommand extends AbstractPluginCommand
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('codechecker')
            ->setDescription('Run Moodle Code Checker on a plugin')
            ->addOption('standard', 's', InputOption::VALUE_OPTIONAL, 'The name or path of the coding standard to use');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->initializeExecute($output, $this->getHelper('process'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHeading($output, 'Moodle Code Checker on %s');

        $standard = $input->getOption('standard') ?: $this->resolveStandard();
        $files    = $this->plugin->getFiles(
            Finder::create()->notPath('yui/build')->name('*.php')->name('*.js')->notName('*-min.js')
        );

        $sniffer = new \PHP_CodeSniffer();
        $sniffer->setCli(new CodeSnifferCLI([
            'reports'      => ['full' => null],
            'colors'       => true,
            'encoding'     => 'utf-8',
            'showProgress' => true,
            'reportWidth'  => 120,
        ]));

        $sniffer->process($files, $standard);
        $results = $sniffer->reporting->printReport('full', false, $sniffer->cli->getCommandLineValues(), null, 120);

        return $results['errors'] > 0 ? 1 : 0;
    }

    /**
     * Find the Moodle coding standard.
     *
     * @param array|null $locations Override the default locations to search
     *
     * @return string
     */
    public function resolveStandard(array $locations = null)
    {
        $locations = $locations ?: [
            __DIR__.'/../../../../moodlehq/codechecker/moodle', // Global Composer install.
            __DIR__.'/../../vendor/moodlehq/codechecker/moodle', // Local Composer install.
        ];

        foreach ($locations as $location) {
            if (file_exists($location)) {
                return $location;
            }
        }

        throw new \RuntimeException('Failed to find the Moodle coding standard, likely need to run Composer install');
    }
}
