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

namespace MoodlePluginCI\Tests\PluginValidate\Finder;

use MoodlePluginCI\PluginValidate\Finder\FileTokens;
use MoodlePluginCI\PluginValidate\Finder\TablePrefixFinder;

class TablePrefixFinderTest extends \PHPUnit\Framework\TestCase
{
    public function testFindTokens(): void
    {
        $file       = __DIR__ . '/../../Fixture/moodle-local_ci/db/install.xml';
        $fileTokens = FileTokens::create('db/install.xml')->mustHave('local_ci');

        $finder = new TablePrefixFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertTrue($fileTokens->hasFoundAllTokens());
    }

    public function testFindTokensFail(): void
    {
        $file       = __DIR__ . '/../../Fixture/bad-install.xml';
        $fileTokens = FileTokens::create('db/install.xml')->mustHave('local_ci');

        $finder = new TablePrefixFinder();
        $finder->findTokens($file, $fileTokens);

        $this->assertFalse($fileTokens->hasFoundAllTokens());
    }
}
