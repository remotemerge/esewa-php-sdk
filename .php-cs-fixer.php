<?php declare(strict_types=1);

// directories and files
$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/demo',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('php');

// init config
$config = new PhpCsFixer\Config();
$config->setUsingCache(false)
    ->setIndent('    ')
    ->setRiskyAllowed(true);

// set rules
return $config->setRules([
    '@PSR1' => true,
    '@PSR12:risky' => true,
    '@PHP74Migration:risky' => true,
    // custom
    'single_quote' => true,
])->setFinder($finder);
