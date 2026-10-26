<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Tests\Functional;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * TCA needs no migration on the running TYPO3 version, i.e. uses no outdated syntax
 */
final class TcaTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content'];

    protected array $testExtensionsToLoad = ['web-nomads/wn-raisenow'];

    #[Test]
    public function ownTablesNeedNoMigration(): void
    {
        $tca = [
            'tx_wnraisenow_form' => require __DIR__ . '/../../Configuration/TCA/tx_wnraisenow_form.php',
            'tx_wnraisenow_purpose' => require __DIR__ . '/../../Configuration/TCA/tx_wnraisenow_purpose.php',
        ];

        self::assertSame([], $this->migrationMessages($tca));
    }

    #[Test]
    public function contentElementIsRegistered(): void
    {
        $tca = $GLOBALS['TCA']['tt_content'];
        $cTypes = array_map(
            static fn(array $item): string => (string)($item['value'] ?? $item[1] ?? ''),
            $tca['columns']['CType']['config']['items']
        );

        self::assertContains('wnraisenow_form', $cTypes);
        self::assertStringContainsString('tx_wnraisenow_form', $tca['types']['wnraisenow_form']['showitem']);
        self::assertSame('wn-raisenow-content', $tca['ctrl']['typeicon_classes']['wnraisenow_form']);
    }

    #[Test]
    public function newContentWizardEntryIsAddedByPageTsconfigBeforeVersionThirteen(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/DonationForms.csv');
        $elements = BackendUtility::getPagesTSconfig(1)['mod.']['wizards.']['newContentElement.']['wizardItems.']['plugins.']['elements.'] ?? [];

        if ((new Typo3Version())->getMajorVersion() < 13) {
            self::assertSame('wn-raisenow-content', $elements['wnraisenow_form.']['iconIdentifier'] ?? null);
            self::assertSame('wnraisenow_form', $elements['wnraisenow_form.']['tt_content_defValues.']['CType'] ?? null);
        } else {
            self::assertArrayNotHasKey('wnraisenow_form.', $elements);
        }
    }

    /**
     * @param array<string, mixed> $tca
     * @return list<string>
     */
    private function migrationMessages(array $tca): array
    {
        // namespace moved in v13, result object since v14
        $className = class_exists(\TYPO3\CMS\Core\Configuration\Tca\TcaMigration::class)
            ? \TYPO3\CMS\Core\Configuration\Tca\TcaMigration::class
            : 'TYPO3\\CMS\\Core\\Migrations\\TcaMigration';
        /** @var mixed $migration */
        $migration = new $className(); // @phpstan-ignore class.notFound (TYPO3 12 class name)
        $result = $migration->migrate($tca);

        return array_values(is_array($result) ? $migration->getMessages() : $result->getMessages());
    }
}
