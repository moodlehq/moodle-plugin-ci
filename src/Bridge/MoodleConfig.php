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

namespace Moodlerooms\MoodlePluginCI\Bridge;

use Moodlerooms\MoodlePluginCI\Installer\Database\AbstractDatabase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Builds and interacts with the content of the Moodle config file.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleConfig
{
    const PLACEHOLDER = '// Extra config.';

    /**
     * Create a Moodle config.
     *
     * @param AbstractDatabase $database
     * @param string           $dataDir  Absolute path to data directory
     *
     * @return string
     */
    public function createContents(AbstractDatabase $database, $dataDir)
    {
        $template  = file_get_contents(__DIR__.'/../../res/template/config.php.txt');
        $variables = [
            '{{DBTYPE}}'          => $database->type,
            '{{DBLIBRARY}}'       => $database->library,
            '{{DBHOST}}'          => $database->host,
            '{{DBNAME}}'          => $database->name,
            '{{DBUSER}}'          => $database->user,
            '{{DBPASS}}'          => $database->pass,
            '{{WWWROOT}}'         => 'http://localhost/moodle',
            '{{DATAROOT}}'        => $dataDir,
            '{{PHPUNITDATAROOT}}' => $dataDir.'/phpu_moodledata',
            '{{BEHATDATAROOT}}'   => $dataDir.'/behat_moodledata',
            '{{BEHATWWWROOT}}'    => 'http://localhost:8000',
            '{{EXTRACONFIG}}'     => self::PLACEHOLDER,
        ];

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Adds a line of PHP code into the config file.
     *
     * @param string $contents  The config file contents
     * @param string $lineToAdd The line to inject
     *
     * @return string
     */
    public function injectLine($contents, $lineToAdd)
    {
        if (strpos($contents, self::PLACEHOLDER) === false) {
            throw new \RuntimeException('Failed to find placeholder in config file, file might be malformed');
        }

        return str_replace(self::PLACEHOLDER, $lineToAdd."\n".self::PLACEHOLDER, $contents);
    }

    /**
     * Read a config file.
     *
     * @param string $file Path to the file to read
     *
     * @return string
     */
    public function read($file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException('Failed to find Moodle config.php file, perhaps Moodle has not been installed yet');
        }

        // Must suppress as unreadable files emit PHP warning, but we handle it below.
        $contents = @file_get_contents($file);

        if ($contents === false) {
            throw new \RuntimeException('Failed to read from the Moodle config.php file');
        }

        return $contents;
    }

    /**
     * Write the config file contents out to the config file.
     *
     * @param string $file     File path
     * @param string $contents Config file contents
     */
    public function dump($file, $contents)
    {
        $filesystem = new Filesystem();
        $filesystem->dumpFile($file, $contents);
        $filesystem->chmod($file, 0644);
    }
}
