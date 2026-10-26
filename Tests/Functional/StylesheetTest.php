<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Default stylesheet, its replacement and removal via settings.stylesheet
 */
final class StylesheetTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content'];

    protected array $testExtensionsToLoad = [
        'web-nomads/wn-raisenow',
        __DIR__ . '/Fixtures/Extensions/wn_raisenow_styles',
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
    }

    #[Test]
    public function defaultStylesheetIsIncludedWithTheWidget(): void
    {
        $html = $this->render();

        self::assertMatchesRegularExpression('#<link [^>]*href="[^"]*Css/raisenow\.css[^"]*"#', $html);
    }

    #[Test]
    public function defaultStylesheetIsNotIncludedWithoutWidget(): void
    {
        self::assertStringNotContainsString('raisenow.css', $this->render(null, 'https://website.local/versteckt'));
    }

    #[Test]
    public function ownStylesheetReplacesTheDefault(): void
    {
        $html = $this->render('custom.typoscript');

        self::assertMatchesRegularExpression('#<link [^>]*href="[^"]*Css/custom\.css[^"]*"#', $html);
        self::assertStringNotContainsString('raisenow.css', $html);
    }

    #[Test]
    public function emptySettingIncludesNoStylesheet(): void
    {
        $html = $this->render('none.typoscript');

        self::assertStringContainsString('data-wn-raisenow=', $html);
        self::assertStringNotContainsString('raisenow.css', $html);
    }

    #[Test]
    public function scssIsSkippedWithoutWsScss(): void
    {
        $html = $this->render('scss.typoscript');

        self::assertStringContainsString('data-wn-raisenow=', $html);
        self::assertStringNotContainsString('custom.scss', $html);
    }

    private function render(?string $typoScriptFixture = null, string $url = 'https://website.local/'): string
    {
        $setup = [
            'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
            'EXT:wn_raisenow/Tests/Functional/Fixtures/page.typoscript',
        ];
        if ($typoScriptFixture !== null) {
            $setup[] = 'EXT:wn_raisenow/Tests/Functional/Fixtures/TypoScript/' . $typoScriptFixture;
        }
        $this->setUpFrontendRootPage(1, [
            'constants' => ['EXT:fluid_styled_content/Configuration/TypoScript/constants.typoscript'],
            'setup' => $setup,
        ]);
        $response = $this->executeFrontendSubRequest(new InternalRequest($url));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }
}
