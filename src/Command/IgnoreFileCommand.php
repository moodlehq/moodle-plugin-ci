<?php
/**
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Command;

use Moodlerooms\MoodlePluginCI\Validate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Creates a properties file for Phing with details about the plugin.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class IgnoreFileCommand extends Command
{
    protected function configure()
    {
        $this->setName('ignorefile')
            ->setDescription('Create a ignore file in a plugin')
            ->addArgument('plugin', InputArgument::REQUIRED, 'Path to the plugin')
            ->addOption('not-paths', null, InputOption::VALUE_OPTIONAL, 'CSV of file paths to exclude')
            ->addOption('not-names', null, InputOption::VALUE_OPTIONAL, 'CSV of file names to exclude');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validate = new Validate();
        $plugin   = realpath($validate->directory($input->getArgument('plugin')));
        $notPaths = $this->csvToArray($input->getOption('not-paths'));
        $notNames = $this->csvToArray($input->getOption('not-names'));

        $out = $plugin.'/.travis-ignore.yml';

        if (file_exists($out)) {
            $output->writeln('Ignore file already exists in plugin, skipping creation of ignore file.');

            return 0;
        }

        $ignores = [];

        if (!empty($notPaths)) {
            $ignores['notPaths'] = $notPaths;
        }
        if (!empty($notNames)) {
            $ignores['notNames'] = $notNames;
        }
        if (empty($ignores)) {
            $output->writeln('<comment>No file ignores to write out, skipping creation of ignore file.</comment>');

            return 0;
        }

        $dump = Yaml::dump($ignores);

        $fs = new Filesystem();
        $fs->dumpFile($out, $dump);

        $output->writeln(sprintf('<info>Created ignore file at %s with the following content:</info>', $out));
        $output->writeln($dump);

        return 0;
    }

    /**
     * Convert a CSV string to an array.
     *
     * Remove empties and surrounding spaces.
     *
     * @param string|null $value
     * @return array
     */
    public function csvToArray($value)
    {
        if ($value === null) {
            return [];
        }

        $result = explode(',', $value);
        $result = array_map('trim', $result);
        $result = array_filter($result);

        return array_values($result);
    }
}
