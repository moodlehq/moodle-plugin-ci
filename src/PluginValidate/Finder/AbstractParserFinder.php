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

namespace Moodlerooms\MoodlePluginCI\PluginValidate\Finder;

use Moodlerooms\MoodlePluginCI\Parser\CodeParser;
use Moodlerooms\MoodlePluginCI\Parser\StatementFilter;

/**
 * Abstract finder.
 *
 * Parses PHP files to find tokens within them.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class AbstractParserFinder implements FinderInterface
{
    /**
     * @var CodeParser
     */
    protected $parser;

    /**
     * @var StatementFilter
     */
    protected $filter;

    public function __construct(CodeParser $parser = null, StatementFilter $filter = null)
    {
        $this->parser = $parser ?: new CodeParser();
        $this->filter = $filter ?: new StatementFilter();
    }
}
