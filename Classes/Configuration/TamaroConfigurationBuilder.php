<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Configuration;

/**
 * Turns a donation form record into the runtime configuration passed to
 * window.rnw.tamaro.runWidget().
 *
 * Empty fields are left out on purpose: whatever RaiseNow configured for the
 * widget stays in effect unless the editor overrides it.
 */
final class TamaroConfigurationBuilder
{
    public const WIDGET_BASE_URL = 'https://tamaro.raisenow.com';

    public const LANGUAGES = ['de', 'en', 'fr', 'it', 'rm', 'es', 'nl', 'hu', 'ro'];
    public const CURRENCIES = ['chf', 'eur', 'usd'];
    public const PAYMENT_TYPES = ['onetime', 'recurring'];
    public const RECURRING_INTERVALS = ['monthly', 'quarterly', 'semestral', 'yearly'];
    public const PAYMENT_METHODS = [
        'card',
        'twint',
        'pfc',
        'pef',
        'paypal',
        'apple_pay',
        'google_pay',
        'chqr',
        'direct_debit',
        'sod',
        'sepa_dd',
    ];

    /**
     * @param array<string, mixed> $form Donation form record, already language overlaid
     * @param list<array<string, mixed>> $purposes Purpose records of the form, already language overlaid
     * @param string|null $siteLanguageCode Two-letter code of the current site language
     * @return array<string, mixed>
     */
    public function build(array $form, array $purposes, ?string $siteLanguageCode): array
    {
        $config = [];

        $language = $this->resolveLanguage((string)($form['language'] ?? ''), $siteLanguageCode);
        if ($language !== null) {
            $config['language'] = $language;
        }
        if ((bool)($form['test_mode'] ?? false)) {
            $config['testMode'] = true;
        }

        $this->addList($config, 'currencies', $form['currencies'] ?? '', self::CURRENCIES);
        $this->addOption($config, 'defaultCurrency', $form['default_currency'] ?? '', self::CURRENCIES);
        $this->addList($config, 'paymentTypes', $form['payment_types'] ?? '', self::PAYMENT_TYPES);
        $this->addOption($config, 'defaultPaymentType', $form['default_payment_type'] ?? '', self::PAYMENT_TYPES);
        $this->addList($config, 'recurringIntervals', $form['recurring_intervals'] ?? '', self::RECURRING_INTERVALS);
        $this->addOption($config, 'defaultRecurringInterval', $form['default_recurring_interval'] ?? '', self::RECURRING_INTERVALS);

        $amounts = $this->buildAmounts((string)($form['amounts_onetime'] ?? ''), (string)($form['amounts_recurring'] ?? ''));
        if ($amounts !== null) {
            $config['amounts'] = $amounts;
        }
        $this->addAmount($config, 'defaultAmount', $form['default_amount'] ?? null);
        $allowCustomAmount = (string)($form['allow_custom_amount'] ?? '');
        if ($allowCustomAmount === '1' || $allowCustomAmount === '0') {
            $config['allowCustomAmount'] = $allowCustomAmount === '1';
        }
        $this->addAmount($config, 'minimumCustomAmount', $form['minimum_custom_amount'] ?? null);
        $this->addAmount($config, 'maximumCustomAmount', $form['maximum_custom_amount'] ?? null);
        if ((bool)($form['highlight_recurring'] ?? false)) {
            $config['highlightRecurring'] = true;
        }

        $this->addList($config, 'paymentMethods', $form['payment_methods'] ?? '', self::PAYMENT_METHODS);
        $this->addOption($config, 'defaultPaymentMethod', $form['default_payment_method'] ?? '', self::PAYMENT_METHODS);

        $this->addPurposes($config, $purposes, (string)($form['default_purpose'] ?? ''), $language);

        foreach ($this->decodeAdditionalConfiguration((string)($form['additional_config'] ?? '')) as $key => $value) {
            $config[$key] = $value;
        }

        return $config;
    }

    /**
     * @return string|null Null if the widget ID or version is not a plain path segment
     */
    public function buildWidgetUrl(string $widgetId, string $version): ?string
    {
        $widgetId = trim($widgetId);
        $version = trim($version) === '' ? 'latest' : trim($version);
        if (!$this->isPathSegment($widgetId) || !$this->isPathSegment($version)) {
            return null;
        }

        return self::WIDGET_BASE_URL . '/' . $widgetId . '/' . $version . '/widget.js';
    }

    /**
     * @return list<int|float>
     */
    public function parseAmounts(string $value): array
    {
        $amounts = [];
        foreach (preg_split('/[\s,;]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $candidate) {
            if (!is_numeric($candidate) || (float)$candidate <= 0) {
                continue;
            }
            $amounts[] = $this->normaliseNumber((float)$candidate);
        }

        return $amounts;
    }

    private function resolveLanguage(string $formLanguage, ?string $siteLanguageCode): ?string
    {
        if (in_array($formLanguage, self::LANGUAGES, true)) {
            return $formLanguage;
        }
        $siteLanguageCode = strtolower((string)$siteLanguageCode);

        return in_array($siteLanguageCode, self::LANGUAGES, true) ? $siteLanguageCode : null;
    }

    /**
     * @return list<int|float>|list<array{if: string, then: list<int|float>}>|null
     */
    private function buildAmounts(string $onetime, string $recurring): ?array
    {
        $onetimeAmounts = $this->parseAmounts($onetime);
        $recurringAmounts = $this->parseAmounts($recurring);

        if ($onetimeAmounts !== [] && $recurringAmounts !== []) {
            return [
                ['if' => 'paymentType() == onetime', 'then' => $onetimeAmounts],
                ['if' => 'paymentType() == recurring', 'then' => $recurringAmounts],
            ];
        }
        // one list only: applies to both payment types
        $amounts = $onetimeAmounts !== [] ? $onetimeAmounts : $recurringAmounts;

        return $amounts !== [] ? $amounts : null;
    }

    /**
     * @param array<string, mixed> $config
     * @param list<array<string, mixed>> $purposes
     */
    private function addPurposes(array &$config, array $purposes, string $defaultPurpose, ?string $language): void
    {
        $keys = [];
        $labels = [];
        $details = [];
        foreach ($purposes as $purpose) {
            $key = trim((string)($purpose['identifier'] ?? ''));
            if (!$this->isPathSegment($key) || in_array($key, $keys, true)) {
                continue;
            }
            $keys[] = $key;
            $label = trim((string)($purpose['label'] ?? ''));
            if ($label !== '') {
                $labels[$key] = $label;
            }
            $campaign = array_filter([
                'stored_campaign_id' => trim((string)($purpose['campaign_id'] ?? '')),
                'stored_campaign_subid' => trim((string)($purpose['campaign_subid'] ?? '')),
            ], static fn(string $value): bool => $value !== '');
            if ($campaign !== []) {
                $details[$key] = $campaign;
            }
        }
        if ($keys === []) {
            return;
        }

        $config['purposes'] = $keys;
        $defaultPurpose = trim($defaultPurpose);
        if (in_array($defaultPurpose, $keys, true)) {
            $config['defaultPurpose'] = $defaultPurpose;
        }
        if ($details !== []) {
            $config['purposeDetails'] = $details;
        }
        if ($labels !== []) {
            // unknown widget language: label every language rather than fall back to "p1"
            foreach ($language !== null ? [$language] : self::LANGUAGES as $code) {
                $config['translations'][$code]['purposes'] = $labels;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeAdditionalConfiguration(string $json): array
    {
        if (trim($json) === '') {
            return [];
        }
        try {
            $decoded = json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }
        if (!is_array($decoded) || array_is_list($decoded)) {
            return [];
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $config
     * @param list<string> $allowed
     */
    private function addList(array &$config, string $key, mixed $value, array $allowed): void
    {
        $values = array_values(array_unique(array_filter(
            array_map('trim', explode(',', (string)$value)),
            static fn(string $item): bool => in_array($item, $allowed, true)
        )));
        if ($values !== []) {
            $config[$key] = $values;
        }
    }

    /**
     * @param array<string, mixed> $config
     * @param list<string> $allowed
     */
    private function addOption(array &$config, string $key, mixed $value, array $allowed): void
    {
        $value = trim((string)$value);
        if (in_array($value, $allowed, true)) {
            $config[$key] = $value;
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function addAmount(array &$config, string $key, mixed $value): void
    {
        if (is_numeric($value) && (float)$value > 0) {
            $config[$key] = $this->normaliseNumber((float)$value);
        }
    }

    private function normaliseNumber(float $value): int|float
    {
        return floor($value) === $value ? (int)$value : round($value, 2);
    }

    private function isPathSegment(string $value): bool
    {
        return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $value) === 1 && !str_contains($value, '..');
    }
}
