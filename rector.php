<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();

    // set custom paths
    $rectorConfig->paths([
        __DIR__ . '/demo',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    // define rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74,
        PHPUnitSetList::PHPUNIT_91,
        SetList::DEAD_CODE,
        SetList::CODING_STYLE,
    ]);
};
