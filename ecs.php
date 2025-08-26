<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return ECSConfig::configure()
    ->withSpacing(
        Option::INDENTATION_SPACES,
        PHP_EOL
    )
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/test',
    ])
    ->withPreparedSets(
        psr12: true, common: true, symplify: true
    )
    ->withRules([
    ]);
