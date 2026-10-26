<?php

declare(strict_types=1);

namespace WebNomads\WnRaisenow\Tests\Unit\Frontend;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Page\PageRenderer;
use WebNomads\WnRaisenow\Frontend\StylesheetIncluder;

final class StylesheetIncluderTest extends TestCase
{
    #[Test]
    public function cssFileIsAddedToThePage(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects(self::once())->method('addCssFile')->with('EXT:site/Resources/Public/Css/donate.css');

        $this->subject($pageRenderer, false)->include(' EXT:site/Resources/Public/Css/donate.css ');
    }

    #[Test]
    public function emptySettingAddsNothing(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects(self::never())->method('addCssFile');

        $this->subject($pageRenderer, true)->include('  ');
    }

    #[Test]
    public function scssIsAddedWhenWsScssIsActive(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects(self::once())->method('addCssFile')->with('EXT:site/Resources/Private/Scss/donate.scss');

        $this->subject($pageRenderer, true)->include('EXT:site/Resources/Private/Scss/donate.scss');
    }

    #[Test]
    public function scssIsSkippedWithoutWsScss(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects(self::never())->method('addCssFile');

        $this->subject($pageRenderer, false)->include('EXT:site/Resources/Private/Scss/donate.SCSS');
    }

    private function subject(PageRenderer $pageRenderer, bool $wsScssActive): StylesheetIncluder
    {
        $packageManager = $this->createMock(PackageManager::class);
        $packageManager->method('isPackageActive')->with('ws_scss')->willReturn($wsScssActive);
        $subject = new StylesheetIncluder($pageRenderer, $packageManager);
        $subject->setLogger(new NullLogger());

        return $subject;
    }
}
