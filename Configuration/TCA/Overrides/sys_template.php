<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// TYPO3 12 and sites without site sets
ExtensionManagementUtility::addStaticFile('wn_raisenow', 'Configuration/TypoScript/', 'RaiseNow donation forms');
