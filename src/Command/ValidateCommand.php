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

namespace MoodlePluginCI\Command;

use MoodlePluginCI\PluginValidate\Plugin;
use MoodlePluginCI\PluginValidate\PluginValidate;
use MoodlePluginCI\PluginValidate\Requirements\RequirementsResolver;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Validate the plugin structure.
 */
class ValidateCommand extends AbstractMoodleCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('validate')
            ->setDescription('Validate a plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputHeading($output, 'Validating %s');

        list($type, $name) = $this->moodle->normalizeComponent($this->plugin->getComponent());

        $plugin       = new Plugin($this->plugin->getComponent(), $type, $name, $this->plugin->directory);
        $resolver     = new RequirementsResolver();
        $requirements = $resolver->resolveRequirements($plugin, $this->moodle->getBranch());

        $validate = new PluginValidate($plugin, $requirements);
        $validate->verifyRequirements();

        $output->writeln($validate->messages);

        return $validate->isValid ? 0 : 1;
    }
}
