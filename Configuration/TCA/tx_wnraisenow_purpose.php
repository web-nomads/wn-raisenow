<?php

declare(strict_types=1);

$ll = 'LLL:EXT:wn_raisenow/Resources/Private/Language/locallang_db.xlf:';
$table = 'tx_wnraisenow_purpose';

return [
    'ctrl' => [
        'title' => $ll . $table,
        'label' => 'label',
        'label_alt' => 'identifier',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'hideTable' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'typeicon_classes' => [
            'default' => 'wn-raisenow-purpose',
        ],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [['label' => '', 'value' => 0]],
                'foreign_table' => $table,
                'foreign_table_where' => 'AND {#' . $table . '}.{#pid}=###CURRENT_PID### AND {#' . $table . '}.{#sys_language_uid} IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_source' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [['label' => '', 'invertStateDisplay' => true]],
            ],
        ],
        'form' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        'identifier' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.identifier',
            'description' => $ll . $table . '.identifier.description',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 50,
                'required' => true,
                'eval' => 'trim,alphanum_x',
                'placeholder' => 'p1',
            ],
        ],
        'label' => [
            'label' => $ll . $table . '.label',
            'description' => $ll . $table . '.label.description',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 255,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
        'campaign_id' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.campaign_id',
            'description' => $ll . $table . '.campaign_id.description',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 100,
                'eval' => 'trim',
            ],
        ],
        'campaign_subid' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.campaign_subid',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'max' => 100,
                'eval' => 'trim',
            ],
        ],
    ],
    'palettes' => [
        'purpose' => [
            'showitem' => 'identifier, label',
        ],
        'campaign' => [
            'showitem' => 'campaign_id, campaign_subid',
        ],
        'language' => [
            'showitem' => 'sys_language_uid, l10n_parent, hidden',
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '--palette--;;purpose, --palette--;' . $ll . $table . '.palette.campaign;campaign, --palette--;;language',
        ],
    ],
];
