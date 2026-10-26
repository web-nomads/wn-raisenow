<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Tests\Unit\Configuration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use WebNomads\WnRaisenow\Configuration\TamaroConfigurationBuilder;

final class TamaroConfigurationBuilderTest extends TestCase
{
    private TamaroConfigurationBuilder $subject;

    protected function setUp(): void
    {
        $this->subject = new TamaroConfigurationBuilder();
    }

    #[Test]
    public function emptyFormLeavesTheWidgetConfigurationAlone(): void
    {
        self::assertSame([], $this->subject->build(['default_amount' => '0.00', 'allow_custom_amount' => ''], [], null));
    }

    #[Test]
    public function pageLanguageIsUsedWhenTamaroSupportsIt(): void
    {
        self::assertSame(['language' => 'fr'], $this->subject->build([], [], 'fr'));
        self::assertSame([], $this->subject->build([], [], 'pt'));
    }

    #[Test]
    public function fixedLanguageWinsOverPageLanguage(): void
    {
        self::assertSame(['language' => 'it'], $this->subject->build(['language' => 'it'], [], 'de'));
    }

    #[Test]
    public function testModeIsOnlyEverForcedOn(): void
    {
        self::assertSame(['testMode' => true], $this->subject->build(['test_mode' => 1], [], null));
        self::assertArrayNotHasKey('testMode', $this->subject->build(['test_mode' => 0], [], null));
    }

    #[Test]
    public function selectionsAreFilteredToKnownValuesInTheirOrder(): void
    {
        $config = $this->subject->build([
            'currencies' => 'eur,chf,xyz',
            'default_currency' => 'chf',
            'payment_types' => 'recurring,onetime',
            'default_payment_type' => 'monthly',
            'recurring_intervals' => 'monthly,yearly',
            'default_recurring_interval' => 'yearly',
            'payment_methods' => 'twint,card,bitcoin,twint',
            'default_payment_method' => 'twint',
        ], [], null);

        self::assertSame([
            'currencies' => ['eur', 'chf'],
            'defaultCurrency' => 'chf',
            'paymentTypes' => ['recurring', 'onetime'],
            'recurringIntervals' => ['monthly', 'yearly'],
            'defaultRecurringInterval' => 'yearly',
            'paymentMethods' => ['twint', 'card'],
            'defaultPaymentMethod' => 'twint',
        ], $config);
    }

    #[Test]
    public function separateAmountListsBecomeTamaroConditions(): void
    {
        $config = $this->subject->build(['amounts_onetime' => '20, 50, 100', 'amounts_recurring' => '10;20'], [], null);

        self::assertSame([
            ['if' => 'paymentType() == onetime', 'then' => [20, 50, 100]],
            ['if' => 'paymentType() == recurring', 'then' => [10, 20]],
        ], $config['amounts']);
    }

    #[Test]
    public function singleAmountListAppliesToBothPaymentTypes(): void
    {
        self::assertSame([5, 12.5], $this->subject->build(['amounts_recurring' => '5 12.50 -3 abc'], [], null)['amounts']);
    }

    #[Test]
    public function amountsAndCustomAmountSettingsAreNumeric(): void
    {
        $config = $this->subject->build([
            'default_amount' => '50.00',
            'allow_custom_amount' => '0',
            'minimum_custom_amount' => '5.50',
            'maximum_custom_amount' => '0.00',
            'highlight_recurring' => 1,
        ], [], null);

        self::assertSame([
            'defaultAmount' => 50,
            'allowCustomAmount' => false,
            'minimumCustomAmount' => 5.5,
            'highlightRecurring' => true,
        ], $config);
    }

    #[Test]
    public function purposesCarryLabelsAndCampaigns(): void
    {
        $config = $this->subject->build(['default_purpose' => 'p2'], [
            ['identifier' => 'p1', 'label' => 'Where it is needed most', 'campaign_id' => 'C-1', 'campaign_subid' => ''],
            ['identifier' => 'p2', 'label' => 'Emergency relief', 'campaign_id' => '', 'campaign_subid' => ''],
            ['identifier' => 'p1', 'label' => 'Duplicate key', 'campaign_id' => '', 'campaign_subid' => ''],
            ['identifier' => '../x', 'label' => 'Invalid key', 'campaign_id' => '', 'campaign_subid' => ''],
        ], 'en');

        self::assertSame(['p1', 'p2'], $config['purposes']);
        self::assertSame('p2', $config['defaultPurpose']);
        self::assertSame(['p1' => ['stored_campaign_id' => 'C-1']], $config['purposeDetails']);
        self::assertSame(
            ['en' => ['purposes' => ['p1' => 'Where it is needed most', 'p2' => 'Emergency relief']]],
            $config['translations']
        );
    }

    #[Test]
    public function purposeLabelsCoverEveryLanguageWhenTheWidgetLanguageIsUnknown(): void
    {
        $config = $this->subject->build([], [['identifier' => 'p1', 'label' => 'Help']], null);

        self::assertSame(TamaroConfigurationBuilder::LANGUAGES, array_keys($config['translations']));
        self::assertSame(['p1' => 'Help'], $config['translations']['rm']['purposes']);
    }

    #[Test]
    public function unknownDefaultPurposeIsDropped(): void
    {
        $config = $this->subject->build(['default_purpose' => 'p9'], [['identifier' => 'p1', 'label' => '']], 'de');

        self::assertSame(['language' => 'de', 'purposes' => ['p1']], $config);
    }

    #[Test]
    public function additionalJsonReplacesTopLevelKeys(): void
    {
        $config = $this->subject->build([
            'payment_methods' => 'card,twint',
            'additional_config' => '{"paymentMethods": ["twint"], "showFooter": false}',
        ], [], null);

        self::assertSame(['paymentMethods' => ['twint'], 'showFooter' => false], $config);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function ignoredAdditionalConfiguration(): array
    {
        return [
            'invalid JSON' => ['{"showFooter": false'],
            'list instead of object' => ['["a", "b"]'],
            'scalar' => ['42'],
        ];
    }

    #[Test]
    #[DataProvider('ignoredAdditionalConfiguration')]
    public function additionalJsonThatIsNoObjectIsIgnored(string $json): void
    {
        self::assertSame([], $this->subject->build(['additional_config' => $json], [], null));
    }

    #[Test]
    public function widgetUrlFollowsTheRaiseNowScheme(): void
    {
        self::assertSame(
            'https://tamaro.raisenow.com/my-org-de/latest/widget.js',
            $this->subject->buildWidgetUrl(' my-org-de ', '')
        );
        self::assertSame(
            'https://tamaro.raisenow.com/my-org-de/2.20.1/widget.js',
            $this->subject->buildWidgetUrl('my-org-de', '2.20.1')
        );
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidWidgetReferences(): array
    {
        return [
            'empty ID' => ['', 'latest'],
            'path traversal' => ['..', 'latest'],
            'slash in ID' => ['my-org/../evil', 'latest'],
            'script in version' => ['my-org', '"><script>'],
            'other host' => ['//evil.example', 'latest'],
        ];
    }

    #[Test]
    #[DataProvider('invalidWidgetReferences')]
    public function widgetUrlRejectsAnythingButAPathSegment(string $widgetId, string $version): void
    {
        self::assertNull($this->subject->buildWidgetUrl($widgetId, $version));
    }
}
