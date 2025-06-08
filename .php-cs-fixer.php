<?php declare(strict_types=1);

// directories and files
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('php');

// init config
$config = new PhpCsFixer\Config();
$config->setUsingCache(false)->setRiskyAllowed(true);

// set rules
return $config->setRules([
    '@PER-CS' => true,
    '@PER-CS:risky' => true,
    '@PHP81Migration' => true,
    '@PHPUnit100Migration:risky' => true,
    // Additional rules
    'declare_strict_types' => true,
    'no_unused_imports' => true,
    'ordered_imports' => true,
    'phpdoc_order' => true,
    'single_quote' => true,
    'ternary_to_null_coalescing' => true,
])->setFinder($finder);
