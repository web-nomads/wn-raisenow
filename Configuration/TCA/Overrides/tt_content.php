<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$ll = 'LLL:EXT:wn_raisenow/Resources/Private/Language/locallang_db.xlf:';
$cType = 'wnraisenow_form';

ExtensionManagementUtility::addTCAcolumns('tt_content', [
    'tx_wnraisenow_form' => [
        'label' => $ll . 'tt_content.tx_wnraisenow_form',
        'description' => $ll . 'tt_content.tx_wnraisenow_form.description',
        'config' => [
            'type' => 'group',
            'allowed' => 'tx_wnraisenow_form',
            'size' => 1,
            'minitems' => 1,
            'maxitems' => 1,
            'default' => '',
        ],
    ],
]);

ExtensionManagementUtility::addTcaSelectItem('tt_content', 'CType', [
    'label' => $ll . 'tt_content.CType.' . $cType,
    'description' => $ll . 'tt_content.CType.' . $cType . '.description',
    'value' => $cType,
    'icon' => 'wn-raisenow-content',
    'group' => 'plugins',
]);

$GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$cType] = 'wn-raisenow-content';
$GLOBALS['TCA']['tt_content']['types'][$cType] = [
    'showitem' => '
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
            --palette--;;general,
            --palette--;;headers,
            tx_wnraisenow_form,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
            --palette--;;frames,
            --palette--;;appearanceLinks,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
            --palette--;;language,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
            --palette--;;hidden,
            --palette--;;access,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
            rowDescription,
        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,
    ',
];
