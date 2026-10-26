<?php

declare(strict_types=1);

$config = \TYPO3\CodingStandards\CsFixerConfig::create();

$config->getFinder()
    ->in(__DIR__)
    ->exclude(['.Build', 'var', 'Resources/Private/PHP/vendor']);

return $config;
