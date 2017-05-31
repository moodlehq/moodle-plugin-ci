<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Moodlerooms\MoodlePluginCI\Tests\Command;

use Moodlerooms\MoodlePluginCI\Command\CodeFixerCommand;
use Moodlerooms\MoodlePluginCI\Tests\Fake\Bridge\DummyMoodlePlugin;
use Moodlerooms\MoodlePluginCI\Tests\MoodleTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Yaml;

class CodeFixerCommandTest extends MoodleTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $content = <<<'EOT'
<?php

if (true) {

} elseif (false) {

}

EOT;

        $config = ['filter' => ['notNames' => ['ignore_name.php'], 'notPaths' => ['ignore']]];
        $this->fs->dumpFile($this->pluginDir.'/.moodle-plugin-ci.yml', Yaml::dump($config));
        $this->fs->dumpFile($this->pluginDir.'/fixable.php', $content
);
    }

    protected function executeCommand($pluginDir = null)
    {
        if ($pluginDir === null) {
            $pluginDir = $this->pluginDir;
        }

        $command          = new CodeFixerCommand();
        $command->plugin  = new DummyMoodlePlugin($pluginDir);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('phpcbf'));
        $commandTester->execute([
            'plugin' => $pluginDir,
        ]);

        return $commandTester;
    }

    public function testExecute()
    {
        $this->expectOutputRegex('/Fixed 1 files/');
        $commandTester = $this->executeCommand();
        $this->assertSame(0, $commandTester->getStatusCode());

        $expected = <<<'EOT'
<?php

if (true) {

} else if (false) {

}

EOT;
        $this->assertSame($expected, file_get_contents($this->pluginDir.'/fixable.php'));
    }
}
