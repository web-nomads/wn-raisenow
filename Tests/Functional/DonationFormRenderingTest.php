<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Content element rendered through the real frontend, in both site languages
 */
final class DonationFormRenderingTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content'];

    protected array $testExtensionsToLoad = ['web-nomads/wn-raisenow'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->writeSite();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DonationForms.csv');
        $this->setUpFrontendRootPage(1, [
            'constants' => ['EXT:fluid_styled_content/Configuration/TypoScript/constants.typoscript'],
            'setup' => [
                'EXT:fluid_styled_content/Configuration/TypoScript/setup.typoscript',
                'EXT:wn_raisenow/Configuration/TypoScript/setup.typoscript',
                'EXT:wn_raisenow/Tests/Functional/Fixtures/page.typoscript',
            ],
        ]);
    }

    #[Test]
    public function defaultLanguageRendersTheWidgetContainerWithItsSettings(): void
    {
        $html = $this->fetch('https://website.local/');
        $settings = $this->extractSettings($html);

        self::assertStringContainsString('id="wn-raisenow-1"', $html);
        self::assertStringContainsString('Resources/Public/JavaScript/tamaro.js', $html);
        self::assertSame('https://tamaro.raisenow.com/my-org-de/latest/widget.js', $settings['widgetUrl']);
        self::assertSame('https://example.org/danke', $settings['resultUrl']);
        self::assertSame([
            'language' => 'de',
            'testMode' => true,
            'currencies' => ['chf'],
            'defaultCurrency' => 'chf',
            'paymentTypes' => ['onetime', 'recurring'],
            'amounts' => [
                ['if' => 'paymentType() == onetime', 'then' => [20, 50, 100]],
                ['if' => 'paymentType() == recurring', 'then' => [10, 20]],
            ],
            'defaultAmount' => 50,
            'allowCustomAmount' => true,
            'paymentMethods' => ['twint', 'card'],
            'purposes' => ['p1', 'p2'],
            'defaultPurpose' => 'p1',
            'purposeDetails' => ['p1' => ['stored_campaign_id' => 'C-100']],
            'translations' => ['de' => ['purposes' => ['p1' => 'Wo am nötigsten', 'p2' => 'Nothilfe']]],
        ], $settings['config']);
    }

    #[Test]
    public function translatedPageUsesTheTranslatedFormAndPurposes(): void
    {
        $settings = $this->extractSettings($this->fetch('https://website.local/en/'));

        self::assertSame('en', $settings['config']['language']);
        self::assertSame('https://example.org/thanks', $settings['resultUrl']);
        self::assertSame(['p1', 'p2'], $settings['config']['purposes']);
        self::assertSame(
            ['en' => ['purposes' => ['p1' => 'Where it is needed most', 'p2' => 'Emergency relief']]],
            $settings['config']['translations']
        );
    }

    #[Test]
    public function hiddenFormRendersNoWidget(): void
    {
        $html = $this->fetch('https://website.local/versteckt');

        self::assertStringContainsString('Verstecktes Formular', $html);
        self::assertStringNotContainsString('data-wn-raisenow', $html);
        self::assertStringNotContainsString('tamaro.js', $html);
    }

    private function fetch(string $url): string
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest($url));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }

    /**
     * @return array<string, mixed>
     */
    private function extractSettings(string $html): array
    {
        self::assertSame(1, preg_match('/data-wn-raisenow="([^"]+)"/', $html, $matches));

        return json_decode(html_entity_decode($matches[1] ?? '', ENT_QUOTES | ENT_HTML5), true, 512, JSON_THROW_ON_ERROR);
    }

    private function writeSite(): void
    {
        $directory = Environment::getConfigPath() . '/sites/website';
        GeneralUtility::mkdir_deep($directory);
        file_put_contents($directory . '/config.yaml', implode("\n", [
            'rootPageId: 1',
            "base: 'https://website.local/'",
            'languages:',
            '  - { languageId: 0, title: Deutsch, enabled: true, base: /, locale: de_CH.UTF-8, flag: ch }',
            '  - { languageId: 1, title: English, enabled: true, base: /en/, locale: en_US.UTF-8, flag: us, fallbackType: strict }',
            '',
        ]));
    }
}
