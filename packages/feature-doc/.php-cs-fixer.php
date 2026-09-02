<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/bin')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
;

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        // Base Symfony style (clean & consistent)
        '@Symfony' => true,
        '@Symfony:risky' => true,

        // Modern PHP strictness
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'static_lambda' => true,
        'return_assignment' => true,

        // Imports & structure
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'ordered_class_elements' => true,
        'psr_autoloading' => true,

        // Arrays
        'array_syntax' => ['syntax' => 'short'],
        'array_indentation' => true,

        // PHPDoc cleanup
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,

        // General cleanliness
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'return',
                'throw',
                'use',
                'parenthesis_brace_block',
                'square_brace_block',
                'curly_brace_block',
            ],
        ],

        // Safer native function calls in internal namespace
        'native_function_invocation' => [
            'include' => ['@internal'],
        ],
    ])
    ->setFinder($finder);
