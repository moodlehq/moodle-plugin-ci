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

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                              => true,
        '@Symfony:risky'                        => true,
        # Couple of changes from Symfony defaults
        'modernize_strpos'                      => false, # TODO: Enable this once PHP8.0 is the min. req.
        'nullable_type_declaration_for_default_null_value' => true, # We prefer explicit typing, no matter the defaults.
        // Psalm only supports phpdoc blocks, not other comments (1-star, slashes, etc.)
        'phpdoc_to_comment' => [
            'ignored_tags' => [
                'psalm-suppress',
            ],
        ],
        # We continue here.
        'array_syntax'                          => ['syntax' => 'short'],
        'combine_consecutive_unsets'            => true,
        'combine_consecutive_issets'            => true,
        'general_phpdoc_annotation_remove'      => ['annotations' => [
                                                       'expectedException',
                                                       'expectedExceptionMessage',
                                                       'expectedExceptionMessageRegExp',
                                                   ]],
        'heredoc_to_nowdoc'                     => true,
        'no_extra_blank_lines'                  => ['tokens' => [
                                                       'break', 'continue', 'extra', 'return',
                                                       'throw', 'use', 'parenthesis_brace_block',
                                                       'square_brace_block', 'curly_brace_block',
                                                   ]],
        'echo_tag_syntax'                       => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else'                       => true,
        'no_useless_return'                     => true,
        'ordered_imports'                       => true,
        'php_unit_strict'                       => true,
        'phpdoc_add_missing_param_annotation'   => true,
        'no_superfluous_phpdoc_tags'            => false,
        'phpdoc_order'                          => true,
        'semicolon_after_instruction'           => true,
        'strict_comparison'                     => true,
        'strict_param'                          => true,
        'binary_operator_spaces'                => ['operators' => ['=' => 'align', '=>' => 'align']],
        'concat_space'                          => ['spacing' => 'one'],
        'align_multiline_comment'               => true,
        'yoda_style'                            => false,
        'compact_nullable_typehint'             => true,
        'native_function_invocation'            => false,
        'native_constant_invocation'            => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('tests/Fixture')
            ->exclude('moodle')
            ->exclude('moodledata')
            ->name('moodle-plugin-ci')
            ->name('validate-version')
            ->in(__DIR__)
    );
