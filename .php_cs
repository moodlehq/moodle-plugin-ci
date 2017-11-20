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

$header = <<<'EOF'
This file is part of the Moodle Plugin CI package.

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.

Copyright (c) 2017 Blackboard Inc. (http://www.blackboard.com)
License http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                              => true,
        '@Symfony:risky'                        => true,
        'array_syntax'                          => ['syntax' => 'short'],
        'combine_consecutive_unsets'            => true,
        'combine_consecutive_issets'            => true,
        'general_phpdoc_annotation_remove'      => ['expectedException', 'expectedExceptionMessage', 'expectedExceptionMessageRegExp'],
        'header_comment'                        => ['header' => $header],
        'heredoc_to_nowdoc'                     => true,
        'no_extra_consecutive_blank_lines'      => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block'],
        'no_short_echo_tag'                     => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else'                       => true,
        'no_useless_return'                     => true,
        'ordered_imports'                       => true,
        'php_unit_strict'                       => true,
        'phpdoc_add_missing_param_annotation'   => true,
        'phpdoc_order'                          => true,
        'semicolon_after_instruction'           => true,
        'strict_comparison'                     => true,
        'strict_param'                          => true,
        'binary_operator_spaces'                => ['align_equals' => true, 'align_double_arrow' => true],
        'align_multiline_comment'               => true,
        'yoda_style'                            => false,
        'compact_nullable_typehint'             => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('tests/Fixture')
            ->exclude('moodle')
            ->exclude('moodledata')
            ->name('moodle-plugin-ci')
            ->in(__DIR__)
    );
