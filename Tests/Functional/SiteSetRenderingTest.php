<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Site as TypoScript root (TYPO3 13+): no sys_template, fluid_styled_content as site set only
 */
final class SiteSetRenderingTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content'];

    protected array $testExtensionsToLoad = ['web-nomads/wn-raisenow'];

    protected function setUp(): void
    {
        parent::setUp();
        if ((new Typo3Version())->getMajorVersion() < 13) {
            self::markTestSkipped('Site sets exist since TYPO3 13');
        }
        $directory = Environment::getConfigPath() . '/sites/website';
        GeneralUtility::mkdir_deep($directory);
        file_put_contents($directory . '/config.yaml', implode("\n", [
            'rootPageId: 1',
            "base: 'https://website.local/'",
            'dependencies:',
            '  - typo3/fluid-styled-content',
            'languages:',
            '  - { languageId: 0, title: Deutsch, enabled: true, base: /, locale: de_CH.UTF-8, flag: ch }',
            '  - { languageId: 1, title: English, enabled: true, base: /en/, locale: en_US.UTF-8, flag: us, fallbackType: strict }',
            '',
        ]));
        file_put_contents($directory . '/setup.typoscript', "page = PAGE\npage.10 < styles.content.get\n");
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DonationForms.csv');
    }

    #[Test]
    public function contentElementRendersWithoutIncludingTheExtensionSet(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('https://website.local/'));
        $html = (string)$response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString('has no rendering definition', $html);
        self::assertStringContainsString('data-wn-raisenow=', $html);
    }
}
