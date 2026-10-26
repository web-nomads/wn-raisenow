<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'RaiseNow donation forms',
    'description' => 'RaiseNow donation forms for TYPO3: configure Tamaro donation widgets in the backend - amounts, currencies, payment methods, purposes, thank-you page - and place them on any page as a content element.',
    'category' => 'fe',
    'author' => 'Marcel Marty',
    'author_email' => 'contact@marcelmarty.ch',
    'state' => 'stable',
    'version' => '1.1.1',
    'constraints' => [
        'depends' => [
            'php' => '8.1.0-8.5.99',
            'typo3' => '12.4.0-14.3.99',
            'backend' => '12.4.0-14.3.99',
            'fluid' => '12.4.0-14.3.99',
            'frontend' => '12.4.0-14.3.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'fluid_styled_content' => '12.4.0-14.3.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'WebNomads\\WnRaisenow\\' => 'Classes/',
        ],
    ],
];
