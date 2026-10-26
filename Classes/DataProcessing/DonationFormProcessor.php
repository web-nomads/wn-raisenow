<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\DataProcessing;

use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use WebNomads\WnRaisenow\Configuration\TamaroConfigurationBuilder;
use WebNomads\WnRaisenow\Domain\Repository\DonationFormRepository;

/**
 * Provides the selected donation form to the content element template.
 *
 * Example:
 *   dataProcessing.10 = WebNomads\WnRaisenow\DataProcessing\DonationFormProcessor
 *   dataProcessing.10.as = raisenow
 *
 * The variable is null if no visible form with a valid widget ID is selected.
 */
final class DonationFormProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly DonationFormRepository $repository,
        private readonly TamaroConfigurationBuilder $builder,
    ) {}

    /**
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $as = (string)($processorConfiguration['as'] ?? 'raisenow');
        $fieldName = (string)($processorConfiguration['fieldName'] ?? 'tx_wnraisenow_form');
        $processedData[$as] = null;

        $form = $this->repository->findVisibleByUid((int)($cObj->data[$fieldName] ?? 0));
        if ($form === null) {
            return $processedData;
        }
        $widgetUrl = $this->builder->buildWidgetUrl((string)$form['widget_id'], (string)$form['widget_version']);
        if ($widgetUrl === null) {
            return $processedData;
        }

        $config = $this->builder->build(
            $form,
            $this->repository->findPurposesOfForm((int)$form['uid']),
            $this->resolveSiteLanguageCode($cObj)
        );
        $settings = [
            'widgetUrl' => $widgetUrl,
            'config' => (object)$config,
        ];
        $resultUrl = $this->buildResultUrl($cObj, (string)($form['result_page'] ?? ''));
        if ($resultUrl !== '') {
            $settings['resultUrl'] = $resultUrl;
        }

        $processedData[$as] = [
            'form' => $form,
            'widgetUrl' => $widgetUrl,
            'config' => $config,
            'settings' => json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ];

        return $processedData;
    }

    private function resolveSiteLanguageCode(ContentObjectRenderer $cObj): ?string
    {
        $language = $cObj->getRequest()->getAttribute('language');
        if (!$language instanceof SiteLanguage) {
            return null;
        }
        $code = $language->getLocale()->getLanguageCode();

        return $code !== '' ? $code : null;
    }

    private function buildResultUrl(ContentObjectRenderer $cObj, string $link): string
    {
        if (trim($link) === '') {
            return '';
        }

        return $cObj->createUrl(['parameter' => $link, 'forceAbsoluteUrl' => true]);
    }
}
