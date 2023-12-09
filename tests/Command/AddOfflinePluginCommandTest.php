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

namespace MoodlePluginCI\Tests\Command;

use MoodlePluginCI\Command\AddOfflinePluginCommand;
use MoodlePluginCI\Process\Execute;
use MoodlePluginCI\Tests\FilesystemTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;

class AddOfflinePluginCommandTest extends FilesystemTestCase
{
    /**
     * Creates a instance for testing the Command class.
     *
     * @return CommandTester the Tester without a DummyExecute Class
     */
    protected function getCommandTester(): CommandTester
    {
        $command          = new AddOfflinePluginCommand($this->tempDir . '/.env');
        $command->execute = new Execute();
        $application      = new Application();
        $application->add($command);

        return new CommandTester($application->find('add-offline-plugin'));
    }

    /**
     * Creates the file and appends content to it.
     *
     * @param string $filename The filename that will be used to append the content
     * @param string $path     The path that contains your $filename
     * @param string $content  The content that will be added to the $filename
     *
     * @return void
     */
    private function createFileAndAppend(string $filename, string $path, string $content): void
    {
        $this->fs->touch("$path/$filename");
        $this->fs->appendToFile("$path/$filename", $content);
    }

    /**
     * Creates and checks out the given branch, creates and appends to the $path/$file given.
     * Stays at the given branch.
     *
     * @param string $filename the filename that will be created
     * @param string $path     the path in which the filename will be created
     * @param string $branch   the branch, in which the file will be added and commited
     *
     * @return void
     */
    private function createFileAndCommit(string $filename, string $path, string $branch): void
    {
        $process = new Process(['git', 'checkout', '-b', $branch], $path, null, null, null);
        $process->mustRun();
        $this->createFileAndAppend($filename, $path, $branch . '_content');
        $process = new Process(['git', 'add', "$filename"], $path, null, null, null);
        $process->mustRun();
        $process = new Process(['git', 'commit', '-m', "Commit to $path", "$filename"], $path, null, null, null);
        $process->mustRun();
    }

    /**
     * Inits a git repo into the given $foldername
     * Creates dev-master branch and commits test_master.txt into it.
     * Creates dev-dummy branch and commits test_dummy.txt into it.
     * Stays at the dev-dummy branch.
     *
     * @param string $folderName the directory that will be used for all git operations
     *
     * @return void
     */
    public function setUpGitAndFiles(string $folderName): void
    {
        $fileNameMasterBranch = 'test_master.txt';
        $fileNameDummyBranch  = 'test_dummy.txt';
        $this->fs->mkdir("$this->tempDir/$folderName");
        $process = new Process(['git', 'init'], "$this->tempDir/$folderName", null, null, null);
        $process->mustRun();
        $process = new Process(['git', 'config', 'user.email', 'you@example.com'], "$this->tempDir/$folderName", null, null, null);
        $process->mustRun();
        $process = new Process(['git', 'config', 'user.name', 'You'], "$this->tempDir/$folderName", null, null, null);
        $process->mustRun();
        $this->createFileAndCommit($fileNameMasterBranch, "$this->tempDir/$folderName", 'dev-master');
        $this->createFileAndCommit($fileNameDummyBranch, "$this->tempDir/$folderName", 'dev-dummy');
    }

    /**
     * Creates two branches, commits a file to every branch and returns to the dev-master, which has only one file in it.
     *
     * @return void
     */
    public function testExecuteGit()
    {
        $reponame      = 'git_repo';
        $projectName   = 'dummy';
        $commandTester = $this->getCommandTester();
        $gitprocess    = new Process(['git', '-v'], "$this->tempDir", null, null, null);
        $gitprocess->run();
        if (!$gitprocess->isSuccessful()) {
            $this->markTestSkipped('No git found, skipped git Test');

            return;
        }

        $this->setUpGitAndFiles($reponame);

        $commandTester->execute([
            'projectname'   => $projectName,
            '--branch'      => 'dev-master',
            '--source'      => "$this->tempDir/$reponame",
            '--storage'     => "$this->tempDir/moodleplgn",
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertTrue(is_dir("$this->tempDir/moodleplgn/$projectName"));
        $this->assertFileExists($this->tempDir . '/.env');
        $this->assertSame(
            sprintf("EXTRA_PLUGINS_DIR=%s/moodleplgn\n", realpath($this->tempDir)),
            file_get_contents($this->tempDir . '/.env')
        );
        $this->assertFileExists("$this->tempDir/moodleplgn/$projectName/test_master.txt");
        $this->assertFileDoesNotExist("$this->tempDir/moodleplgn/$projectName/test_dummy.txt");
    }

    /**
     * Tests the case, that no git repo is present and no --branch argumen is given.
     *
     * @return void
     */
    public function testExecuteNoGit()
    {
        $reponame    = 'repo';
        $projectName = 'dummy';
        $filename    = 'test_file.txt';
        $this->fs->mkdir("$this->tempDir/$reponame");
        $this->createFileAndAppend('test_file.txt', "$this->tempDir/$reponame", 'test_content');

        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'projectname'   => $projectName,
            '--source'      => "$this->tempDir/$reponame",
            '--storage'     => "$this->tempDir/moodleplgn",
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertTrue(is_dir("$this->tempDir/moodleplgn/$projectName"));
        $this->assertFileExists("$this->tempDir/moodleplgn/$projectName/$filename");
    }

    /**
     * Tests the automatic folder name extraction if no project-name is given. No git involved.
     *
     * @return void
     */
    public function testExecuteNoGitNoProjectName()
    {
        $reponame = 'repo_rand_name';
        $filename = 'test_file.txt';
        $this->fs->mkdir("$this->tempDir/$reponame");
        $this->createFileAndAppend($filename, "$this->tempDir/$reponame", 'test_content');

        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--source'  => "$this->tempDir/$reponame",
            '--storage' => "$this->tempDir/moodleplgn",
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());
        $this->assertFileExists("$this->tempDir/moodleplgn/$reponame/$filename");
    }

    /**
     * Tests an invalid git branch.
     *
     * @return void
     */
    public function testNoBranchGit()
    {
        $reponame = 'repo_no_git';
        $this->expectException(\RuntimeException::class);
        $this->fs->mkdir("$this->tempDir/$reponame");
        $this->setUpGitAndFiles($reponame);
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--branch'  => 'dev-blahblah',
            '--source'  => "$this->tempDir/$reponame",
            '--storage' => "$this->tempDir/moodleplgn",
        ]);
    }

    /**
     * Tests an invalid git repo.
     *
     * @return void
     */
    public function testBrokenGit()
    {
        $reponame = 'repo_no_branch';
        $this->expectException(\RuntimeException::class);
        $this->fs->mkdir("$this->tempDir/$reponame");
        $this->setUpGitAndFiles('$reponame');
        $this->fs->remove("$this->tempDir/$reponame/.git");
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            '--branch'  => 'dev-master',
            '--source'  => "$this->tempDir/$reponame",
            '--storage' => "$this->tempDir/moodleplgn",
        ]);
    }
}
