<?php

namespace Moodlerooms\MoodleTravisPlugin\Properties;

use Moodlerooms\MoodleTravisPlugin\Bridge\MoodlePlugin;

/**
 * Generate properties to be consumed by Phing.
 *
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PluginProperties {
    /**
     * Generate properties file content from a Moodle plugin
     *
     * @param MoodlePlugin $plugin
     * @return string
     * @throws \Exception
     */
    public function getPropertiesFromPlugin(MoodlePlugin $plugin) {
        $component = $plugin->getComponent();
        $directory = $plugin->getInstallDirectory();
        $relative  = $plugin->getRelativeInstallDirectory();
        $phpunit   = $plugin->hasUnitTests() ? 'true' : 'false';
        $behat     = $plugin->hasBehatFeatures() ? 'true' : 'false';

        return <<<TEXT
COMPONENT=$component
PLUGIN_DIR=$relative
MOODLE_PLUGIN_DIR=$directory
PHPUNIT_ENABLED=$phpunit
BEHAT_ENABLED=$behat
TEXT;
    }
}
