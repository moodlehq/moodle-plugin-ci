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

namespace Moodlerooms\MoodlePluginCI\Parser;

use PhpParser\Error;
use PhpParser\ParserFactory;

/**
 * Parse PHP files.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class CodeParser
{
    /**
     * Loads the contents of a file.
     *
     * @param string $path File path
     *
     * @return string
     */
    protected function loadFile($path)
    {
        if (pathinfo($path, PATHINFO_EXTENSION) !== 'php') {
            throw new \InvalidArgumentException('Can only parse files with ".php" extensions');
        }
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('Failed to find \'%s\' file.', $path));
        }

        return file_get_contents($path);
    }

    /**
     * Parse a PHP file.
     *
     * @param string $path File path
     *
     * @return \PhpParser\Node[]
     *
     * @throws \Exception
     */
    public function parseFile($path)
    {
        $factory = new ParserFactory();
        $parser  = $factory->create(ParserFactory::PREFER_PHP7);

        try {
            $statements = $parser->parse($this->loadFile($path));
        } catch (Error $e) {
            throw new \RuntimeException(sprintf('Failed to parse %s file due to parse error: %s', $path, $e->getMessage()), 0, $e);
        }
        if ($statements === null) {
            throw new \RuntimeException(sprintf('Failed to parse %s', $path));
        }

        return $statements;
    }
}
