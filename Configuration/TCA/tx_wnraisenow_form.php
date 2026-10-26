<?php

declare(strict_types=1);

$ll = 'LLL:EXT:wn_raisenow/Resources/Private/Language/locallang_db.xlf:';
$table = 'tx_wnraisenow_form';

$items = static function (string $field, array $values, bool $withEmpty = false) use ($ll, $table): array {
    $items = $withEmpty ? [['label' => $ll . $table . '.inherit', 'value' => '']] : [];
    foreach ($values as $value) {
        $items[] = ['label' => $ll . $table . '.' . $field . '.' . $value, 'value' => $value];
    }
    return $items;
};

$languages = ['de', 'en', 'fr', 'it', 'rm', 'es', 'nl', 'hu', 'ro'];
$currencies = ['chf', 'eur', 'usd'];
$paymentTypes = ['onetime', 'recurring'];
$intervals = ['monthly', 'quarterly', 'semestral', 'yearly'];
$paymentMethods = ['card', 'twint', 'pfc', 'pef', 'paypal', 'apple_pay', 'google_pay', 'chqr', 'direct_debit', 'sod', 'sepa_dd'];

$amountField = static fn(string $description): array => [
    'description' => $ll . $table . '.' . $description,
    'config' => [
        'type' => 'number',
        'format' => 'decimal',
        'size' => 10,
        'default' => 0,
        'range' => ['lower' => 0],
    ],
];

return [
    'ctrl' => [
        'title' => $ll . $table,
        'label' => 'title',
        'label_alt' => 'widget_id',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'typeicon_classes' => [
            'default' => 'wn-raisenow-form',
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
        'title' => [
            'label' => $ll . $table . '.title',
            'description' => $ll . $table . '.title.description',
            'config' => [
                'type' => 'input',
                'size' => 40,
                'max' => 255,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
        'widget_id' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.widget_id',
            'description' => $ll . $table . '.widget_id.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 100,
                'required' => true,
                'eval' => 'trim',
                'placeholder' => 'my-organisation-de',
            ],
        ],
        'widget_version' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.widget_version',
            'description' => $ll . $table . '.widget_version.description',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 30,
                'eval' => 'trim',
                'default' => 'latest',
                'placeholder' => 'latest',
            ],
        ],
        'language' => [
            'label' => $ll . $table . '.language',
            'description' => $ll . $table . '.language.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array_merge(
                    [['label' => $ll . $table . '.language.auto', 'value' => '']],
                    $items('language', $languages)
                ),
                'default' => '',
            ],
        ],
        'test_mode' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.test_mode',
            'description' => $ll . $table . '.test_mode.description',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'currencies' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.currencies',
            'description' => $ll . $table . '.multi.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectCheckBox',
                'items' => $items('currency', $currencies),
            ],
        ],
        'default_currency' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.default_currency',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => $items('currency', $currencies, true),
                'default' => '',
            ],
        ],
        'payment_types' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.payment_types',
            'description' => $ll . $table . '.multi.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectCheckBox',
                'items' => $items('payment_type', $paymentTypes),
            ],
        ],
        'default_payment_type' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.default_payment_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => $items('payment_type', $paymentTypes, true),
                'default' => '',
            ],
        ],
        'recurring_intervals' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.recurring_intervals',
            'description' => $ll . $table . '.multi.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectCheckBox',
                'items' => $items('interval', $intervals),
            ],
        ],
        'default_recurring_interval' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.default_recurring_interval',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => $items('interval', $intervals, true),
                'default' => '',
            ],
        ],
        'highlight_recurring' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.highlight_recurring',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ],
        ],
        'amounts_onetime' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.amounts_onetime',
            'description' => $ll . $table . '.amounts.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
                'placeholder' => '20, 50, 100, 200',
            ],
        ],
        'amounts_recurring' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.amounts_recurring',
            'description' => $ll . $table . '.amounts.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
                'placeholder' => '10, 20, 30, 50',
            ],
        ],
        'default_amount' => ['l10n_mode' => 'exclude', 'label' => $ll . $table . '.default_amount'] + $amountField('zero.description'),
        'allow_custom_amount' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.allow_custom_amount',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => $ll . $table . '.inherit', 'value' => ''],
                    ['label' => $ll . $table . '.yes', 'value' => '1'],
                    ['label' => $ll . $table . '.no', 'value' => '0'],
                ],
                'default' => '',
            ],
        ],
        'minimum_custom_amount' => ['l10n_mode' => 'exclude', 'label' => $ll . $table . '.minimum_custom_amount'] + $amountField('zero.description'),
        'maximum_custom_amount' => ['l10n_mode' => 'exclude', 'label' => $ll . $table . '.maximum_custom_amount'] + $amountField('zero.description'),
        'payment_methods' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.payment_methods',
            'description' => $ll . $table . '.payment_methods.description',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'items' => $items('payment_method', $paymentMethods),
                'size' => 8,
                'multiple' => false,
            ],
        ],
        'default_payment_method' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.default_payment_method',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => $items('payment_method', $paymentMethods, true),
                'default' => '',
            ],
        ],
        'purposes' => [
            'label' => $ll . $table . '.purposes',
            'description' => $ll . $table . '.purposes.description',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_wnraisenow_purpose',
                'foreign_field' => 'form',
                'foreign_sortby' => 'sorting',
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                    'useSortable' => true,
                    'showPossibleLocalizationRecords' => true,
                    'showAllLocalizationLink' => true,
                    'showSynchronizationLink' => true,
                    'levelLinksPosition' => 'bottom',
                ],
            ],
        ],
        'default_purpose' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.default_purpose',
            'description' => $ll . $table . '.default_purpose.description',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'max' => 50,
                'eval' => 'trim',
                'placeholder' => 'p1',
            ],
        ],
        'result_page' => [
            'label' => $ll . $table . '.result_page',
            'description' => $ll . $table . '.result_page.description',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['page', 'url'],
                'appearance' => [
                    'browserTitle' => $ll . $table . '.result_page',
                ],
            ],
        ],
        'additional_config' => [
            'l10n_mode' => 'exclude',
            'label' => $ll . $table . '.additional_config',
            'description' => $ll . $table . '.additional_config.description',
            'config' => [
                'type' => 'text',
                'cols' => 60,
                'rows' => 8,
                'fixedFont' => true,
                'enableTabulator' => true,
                'placeholder' => '{"showFooter": false, "salutations": ["ms", "mr"]}',
            ],
        ],
    ],
    'palettes' => [
        'widget' => [
            'showitem' => 'widget_id, widget_version, --linebreak--, language, test_mode',
        ],
        'currency' => [
            'label' => $ll . $table . '.palette.currency',
            'showitem' => 'currencies, default_currency',
        ],
        'paymentType' => [
            'label' => $ll . $table . '.palette.payment_type',
            'showitem' => 'payment_types, default_payment_type, --linebreak--, recurring_intervals, default_recurring_interval, --linebreak--, highlight_recurring',
        ],
        'amounts' => [
            'label' => $ll . $table . '.palette.amounts',
            'showitem' => 'amounts_onetime, amounts_recurring, --linebreak--, default_amount, --linebreak--, allow_custom_amount, minimum_custom_amount, maximum_custom_amount',
        ],
        'language' => [
            'showitem' => 'sys_language_uid, l10n_parent',
        ],
    ],
    'types' => [
        '0' => [
            'showitem' => '
                --div--;' . $ll . $table . '.tab.general,
                    title, --palette--;' . $ll . $table . '.palette.widget;widget,
                --div--;' . $ll . $table . '.tab.amounts,
                    --palette--;;currency,
                    --palette--;;paymentType,
                    --palette--;;amounts,
                --div--;' . $ll . $table . '.tab.payment_methods,
                    payment_methods, default_payment_method,
                --div--;' . $ll . $table . '.tab.purposes,
                    purposes, default_purpose,
                --div--;' . $ll . $table . '.tab.advanced,
                    result_page, additional_config,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                    --palette--;;language,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                    hidden,
            ',
        ],
    ],
];
