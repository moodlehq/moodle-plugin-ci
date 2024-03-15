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

namespace MoodlePluginCI\Tests\PluginValidate\Requirements;

use MoodlePluginCI\PluginValidate\Finder\FileTokens;
use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\PluginValidate\Requirements\DataformatRequirements;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;

class DataformatRequirementsTest extends \PHPUnit\Framework\TestCase
{
    public function testResolveRequirements(): void
    {
        $resolver = new RequirementsResolver();

        $this->assertInstanceOf(
            DataformatRequirements::class,
            $resolver->resolveRequirements(new Plugin('', 'dataformat', '', ''), 29)
        );
    }

    public function testGetRequiredStrings(): void
    {
        $requirements = new DataformatRequirements(new Plugin('dataformat_csv', 'dataformat', 'csv', ''), 29);
        $fileToken    = $requirements->getRequiredStrings();
        $this->assertInstanceOf(FileTokens::class, $fileToken);
        $this->assertSame('lang/en/dataformat_csv.php', $fileToken->file);
    }
}
