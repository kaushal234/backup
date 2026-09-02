<?php

$header = '';

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['config', 'external', 'internal', 'jobs', 'lib', 'mysql', 'tools', 'var', 'vendor'])
;
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'combine_consecutive_unsets' => true,
        'header_comment' => ['header' => $header],
        'array_syntax' => ['syntax' => 'short'],
        'no_extra_blank_lines' => ['tokens' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block']],
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => true,
        'php_unit_strict' => true,
        'psr_autoloading' => true,
        'strict_comparison' => true,
        'declare_strict_types' => true,
        'heredoc_to_nowdoc' => true,
        'mb_str_functions' => false,
        'no_unreachable_default_argument_value' => true,
        'method_argument_space' => ['on_multiline' => 'ignore'],
    ])
    ->setFinder($finder);
