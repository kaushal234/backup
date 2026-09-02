<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([
                __DIR__.'/src',
                __DIR__.'/bin',
                __DIR__.'/tests',
            ])
            ->append([__FILE__]),
    )
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@DoctrineAnnotation' => true,
        'phpdoc_to_comment' => false,
        'align_multiline_comment' => ['comment_type' => 'phpdocs_like'],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'list_syntax' => [
            'syntax' => 'short',
        ],
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'combine_consecutive_unsets' => true,
        'comment_to_phpdoc' => true,
        'compact_nullable_typehint' => true,
        'declare_strict_types' => true,
        'explicit_indirect_variable' => true,
        'fully_qualified_strict_types' => true,
        'multiline_comment_opening_closing' => true,
        'header_comment' => ['header' => ''],
        'heredoc_to_nowdoc' => true,
        'logical_operators' => true,
        'method_argument_space' => ['on_multiline' => 'ignore'],
        'mb_str_functions' => true,
        'no_alternative_syntax' => true,
        'no_extra_blank_lines' => ['tokens' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block']],
        'no_superfluous_elseif' => true,
        'no_superfluous_phpdoc_tags' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'php_unit_method_casing' => ['case' => 'camel_case'],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_strict' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'psr_autoloading' => true,
        // 'return_assignment' => true,
        'static_lambda' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'void_return' => true,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
        ],
    ]);
