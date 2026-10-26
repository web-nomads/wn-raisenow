<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// appended to fluid_styled_content wherever it is included, no site set or static template needed
ExtensionManagementUtility::addTypoScript(
    'wn_raisenow',
    'setup',
    "@import 'EXT:wn_raisenow/Configuration/Sets/RaiseNow/setup.typoscript'",
    'defaultContentRendering'
);
