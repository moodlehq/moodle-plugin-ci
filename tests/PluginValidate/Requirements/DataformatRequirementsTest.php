<?php

/**
 * This file is part of the Moodle Plugin CI package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright 2024 Marina Glancy
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodlePluginCI\Tests\PluginValidate;

use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\PluginValidate\Requirements\DataformatRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class DataformatRequirementsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DataformatRequirements
     */
    private $requirements;

    protected function setUp(): void
    {
        $this->requirements = new DataformatRequirements(new Plugin('dataformat_csv', 'dataformat', 'csv', ''), 29);
    }

    protected function tearDown(): void
    {
        $this->requirements = null;
    }

    public function testResolveRequirements()
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            'MoodlePluginCI\PluginValidate\Requirements\DataformatRequirements',
            $resolver->resolveRequirements(new Plugin('', 'dataformat', '', ''), 29)
        );
    }

    public function testGetRequiredStrings()
    {
        $fileToken = $this->requirements->getRequiredStrings();
        $this->assertInstanceOf('MoodlePluginCI\PluginValidate\Finder\FileTokens', $fileToken);
        $this->assertSame('lang/en/dataformat_csv.php', $fileToken->file);
    }
}
