<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Frontend;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Adds the stylesheet configured for donation forms to the page.
 *
 * Goes through the PageRenderer rather than the AssetCollector, so ws_scss can
 * compile .scss files in its render-preProcess hook.
 */
class StylesheetIncluder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly PackageManager $packageManager,
    ) {}

    /**
     * @param string $file EXT: path or path relative to the public directory, empty for none
     */
    public function include(string $file): void
    {
        $file = trim($file);
        if ($file === '') {
            return;
        }
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'scss' && !$this->packageManager->isPackageActive('ws_scss')) {
            $this->logger?->warning('Donation form stylesheet {file} skipped, SCSS needs the extension ws_scss', ['file' => $file]);
            return;
        }
        $this->pageRenderer->addCssFile($file);
    }
}
