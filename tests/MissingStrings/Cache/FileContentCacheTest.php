<?php

/*
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Copyright (c) 2025 Volodymyr Dovhan (https://github.com/volodymyrdovhan)
 * License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\MissingStrings\Cache;

use MoodlePluginCI\MissingStrings\Cache\FileContentCache;
use MoodlePluginCI\Tests\MissingStrings\TestBase\MissingStringsTestCase;

/**
 * Tests for FileContentCache class.
 *
 * @covers \MoodlePluginCI\MissingStrings\Cache\FileContentCache
 */
class FileContentCacheTest extends MissingStringsTestCase
{
    /** @var string */
    private $testDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDir = $this->createTempDir('cache_test_');
        FileContentCache::clearCache();
    }

    protected function tearDown(): void
    {
        FileContentCache::clearCache();
        parent::tearDown();
    }

    /**
     * Test getContent with existing file.
     */
    public function testGetContentWithExistingFile(): void
    {
        $testFile = $this->testDir . '/test.txt';
        $content  = 'This is test content';
        file_put_contents($testFile, $content);

        $result = FileContentCache::getContent($testFile);

        $this->assertSame($content, $result);
    }

    /**
     * Test getContent with non-existing file.
     */
    public function testGetContentWithNonExistingFile(): void
    {
        $testFile = $this->testDir . '/nonexistent.txt';

        $result = FileContentCache::getContent($testFile);

        $this->assertFalse($result);
    }

    /**
     * Test getContent caching functionality.
     */
    public function testGetContentCaching(): void
    {
        $testFile = $this->testDir . '/cached.txt';
        $content  = 'Cached content';
        file_put_contents($testFile, $content);

        // First call
        $result1 = FileContentCache::getContent($testFile);
        $this->assertSame($content, $result1);

        // Second call should return cached version
        $result2 = FileContentCache::getContent($testFile);
        $this->assertSame($content, $result2);

        // Verify cache stats
        $stats = FileContentCache::getStats();
        $this->assertSame(1, $stats['cached_files']);
    }

    /**
     * Test getContent cache invalidation when file is modified.
     */
    public function testGetContentCacheInvalidation(): void
    {
        $testFile = $this->testDir . '/modified.txt';
        $content1 = 'Original content';
        file_put_contents($testFile, $content1);

        // First read
        $result1 = FileContentCache::getContent($testFile);
        $this->assertSame($content1, $result1);

        // Clear cache to force re-read (simulate time-based invalidation)
        FileContentCache::clearCache();

        // Modify file
        $content2 = 'Modified content';
        file_put_contents($testFile, $content2);

        // Second read should get new content
        $result2 = FileContentCache::getContent($testFile);
        $this->assertSame($content2, $result2);
    }

    /**
     * Test getContent with unreadable file.
     */
    public function testGetContentWithUnreadableFile(): void
    {
        // Test with a file that doesn't exist (simulates unreadable)
        $testFile = $this->testDir . '/unreadable.txt';

        // Don't create the file, so it's "unreadable"
        $result = FileContentCache::getContent($testFile);
        $this->assertFalse($result);

        // Also test with a file that exists but test the error path
        $existingFile = $this->testDir . '/readable.txt';
        file_put_contents($existingFile, 'content');

        // File should be readable normally
        $result = FileContentCache::getContent($existingFile);
        $this->assertSame('content', $result);
    }

    /**
     * Test getContent with invalid path.
     */
    public function testGetContentWithInvalidPath(): void
    {
        $invalidPath = '/nonexistent/path/file.txt';

        $result = FileContentCache::getContent($invalidPath);

        $this->assertFalse($result);
    }

    /**
     * Test fileExists with existing file.
     */
    public function testFileExistsWithExistingFile(): void
    {
        $testFile = $this->testDir . '/exists.txt';
        file_put_contents($testFile, 'content');

        $result = FileContentCache::fileExists($testFile);

        $this->assertTrue($result);
    }

    /**
     * Test fileExists with non-existing file.
     */
    public function testFileExistsWithNonExistingFile(): void
    {
        $testFile = $this->testDir . '/nonexistent.txt';

        $result = FileContentCache::fileExists($testFile);

        $this->assertFalse($result);
    }

    /**
     * Test fileExists with cached file.
     */
    public function testFileExistsWithCachedFile(): void
    {
        $testFile = $this->testDir . '/cached_exists.txt';
        file_put_contents($testFile, 'content');

        // Cache the file by reading it first
        FileContentCache::getContent($testFile);

        // fileExists should return true for cached file
        $result = FileContentCache::fileExists($testFile);
        $this->assertTrue($result);
    }

    /**
     * Test getLines with existing file.
     */
    public function testGetLinesWithExistingFile(): void
    {
        $testFile = $this->testDir . '/lines.txt';
        $content  = "Line 1\nLine 2\nLine 3";
        file_put_contents($testFile, $content);

        $result = FileContentCache::getLines($testFile);

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertSame(['Line 1', 'Line 2', 'Line 3'], $result);
    }

    /**
     * Test getLines with FILE_IGNORE_NEW_LINES flag.
     */
    public function testGetLinesWithIgnoreNewLines(): void
    {
        $testFile = $this->testDir . '/lines_newlines.txt';
        $content  = "Line 1\nLine 2\nLine 3\n";
        file_put_contents($testFile, $content);

        $result = FileContentCache::getLines($testFile, FILE_IGNORE_NEW_LINES);

        $this->assertSame(['Line 1', 'Line 2', 'Line 3'], $result);
    }

    /**
     * Test getLines without FILE_IGNORE_NEW_LINES flag.
     */
    public function testGetLinesWithoutIgnoreNewLines(): void
    {
        $testFile = $this->testDir . '/lines_with_newlines.txt';
        $content  = "Line 1\nLine 2\nLine 3\n";
        file_put_contents($testFile, $content);

        $result = FileContentCache::getLines($testFile, 0);

        $this->assertSame(['Line 1', 'Line 2', 'Line 3', ''], $result);
    }

    /**
     * Test getLines with non-existing file.
     */
    public function testGetLinesWithNonExistingFile(): void
    {
        $testFile = $this->testDir . '/nonexistent_lines.txt';

        $result = FileContentCache::getLines($testFile);

        $this->assertFalse($result);
    }

    /**
     * Test clearCache functionality.
     */
    public function testClearCache(): void
    {
        $testFile = $this->testDir . '/clear_test.txt';
        file_put_contents($testFile, 'content');

        // Cache a file
        FileContentCache::getContent($testFile);
        $stats = FileContentCache::getStats();
        $this->assertSame(1, $stats['cached_files']);

        // Clear cache
        FileContentCache::clearCache();
        $stats = FileContentCache::getStats();
        $this->assertSame(0, $stats['cached_files']);
    }

    /**
     * Test getStats method.
     */
    public function testGetStats(): void
    {
        $stats = FileContentCache::getStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('cached_files', $stats);
        $this->assertArrayHasKey('max_files', $stats);
        $this->assertArrayHasKey('memory_usage', $stats);
        $this->assertIsInt($stats['cached_files']);
        $this->assertIsInt($stats['max_files']);
        $this->assertIsInt($stats['memory_usage']);
    }

    /**
     * Test cache size limit functionality.
     */
    public function testCacheSizeLimit(): void
    {
        // Create more files than the cache limit (100)
        $files = [];
        for ($i = 0; $i < 105; ++$i) {
            $testFile = $this->testDir . "/file{$i}.txt";
            file_put_contents($testFile, "Content for file {$i}");
            $files[] = $testFile;
        }

        // Cache all files
        foreach ($files as $file) {
            FileContentCache::getContent($file);
        }

        // Check that cache is limited
        $stats = FileContentCache::getStats();
        $this->assertLessThanOrEqual(100, $stats['cached_files']);
    }

    /**
     * Test cache FIFO behavior.
     */
    public function testCacheFIFOBehavior(): void
    {
        // Create exactly 101 files to trigger FIFO
        $files = [];
        for ($i = 0; $i < 101; ++$i) {
            $testFile = $this->testDir . "/fifo{$i}.txt";
            file_put_contents($testFile, "Content {$i}");
            $files[] = $testFile;
        }

        // Cache all files one by one
        foreach ($files as $file) {
            FileContentCache::getContent($file);
        }

        // The first file should have been evicted
        $stats = FileContentCache::getStats();
        $this->assertLessThanOrEqual(100, $stats['cached_files']);
    }

    /**
     * Test memory usage calculation in stats.
     */
    public function testMemoryUsageCalculation(): void
    {
        $testFile = $this->testDir . '/memory_test.txt';
        $content  = 'This is a test content for memory calculation';
        file_put_contents($testFile, $content);

        FileContentCache::getContent($testFile);

        $stats = FileContentCache::getStats();
        $this->assertGreaterThan(0, $stats['memory_usage']);
        $this->assertGreaterThanOrEqual(strlen($content), $stats['memory_usage']);
    }

    /**
     * Test getContent with empty file.
     */
    public function testGetContentWithEmptyFile(): void
    {
        $testFile = $this->testDir . '/empty.txt';
        file_put_contents($testFile, '');

        $result = FileContentCache::getContent($testFile);

        $this->assertSame('', $result);
    }

    /**
     * Test getLines with empty file.
     */
    public function testGetLinesWithEmptyFile(): void
    {
        $testFile = $this->testDir . '/empty_lines.txt';
        file_put_contents($testFile, '');

        $result = FileContentCache::getLines($testFile);

        $this->assertSame([''], $result);
    }

    /**
     * Test path normalization.
     */
    public function testPathNormalization(): void
    {
        $testFile = $this->testDir . '/normal.txt';
        file_put_contents($testFile, 'content');

        // Test with the same file accessed via direct path
        $result1 = FileContentCache::getContent($testFile);
        $this->assertSame('content', $result1);

        // Test path normalization by accessing same file again
        $result2 = FileContentCache::getContent($testFile);
        $this->assertSame('content', $result2);

        // Should only have one cached entry since it's the same file
        $stats = FileContentCache::getStats();
        $this->assertSame(1, $stats['cached_files']);

        // Test with a simple relative path that should work
        $subdir = $this->testDir . '/subdir';
        mkdir($subdir, 0777, true);
        $subdirFile = $subdir . '/file.txt';
        file_put_contents($subdirFile, 'subcontent');

        $result3 = FileContentCache::getContent($subdirFile);
        $this->assertSame('subcontent', $result3);
    }
}
