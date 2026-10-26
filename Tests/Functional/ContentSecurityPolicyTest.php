<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Frontend policy allows the widget once CSP is enforced
 */
final class ContentSecurityPolicyTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content'];

    protected array $testExtensionsToLoad = ['web-nomads/wn-raisenow'];

    protected array $configurationToUseInTestInstance = [
        'SYS' => [
            'features' => [
                'security.frontend.enforceContentSecurityPolicy' => true,
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $directory = Environment::getConfigPath() . '/sites/website';
        GeneralUtility::mkdir_deep($directory);
        file_put_contents($directory . '/config.yaml', implode("\n", [
            'rootPageId: 1',
            "base: 'https://website.local/'",
            'languages:',
            '  - { languageId: 0, title: Deutsch, enabled: true, base: /, locale: de_CH.UTF-8, flag: ch }',
            '',
        ]));
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DonationForms.csv');
        $this->setUpFrontendRootPage(1, [
            'constants' => ['EXT:fluid_styled_content/Configuration/TypoScript/constants.typoscript'],
            'setup' => [
                'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
                'EXT:wn_raisenow/Tests/Functional/Fixtures/page.typoscript',
            ],
        ]);
    }

    #[Test]
    public function policyAllowsTamaroAndRaiseNow(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('https://website.local/'));
        $directives = [];
        foreach (explode(';', $response->getHeaderLine('Content-Security-Policy')) as $directive) {
            $parts = preg_split('/\s+/', trim($directive)) ?: [];
            $directives[(string)array_shift($parts)] = $parts;
        }

        self::assertContains('https://tamaro.raisenow.com', $directives['script-src'] ?? []);
        self::assertContains('https://cdn.jsdelivr.net', $directives['script-src'] ?? []);
        self::assertContains('https://assets.raisenow.io', $directives['style-src'] ?? []);
        self::assertContains('https://assets.raisenow.io', $directives['font-src'] ?? []);
        self::assertContains('https://*.raisenow.io', $directives['connect-src'] ?? []);
        self::assertContains('https://pay.datatrans.com', $directives['frame-src'] ?? []);
    }
}
