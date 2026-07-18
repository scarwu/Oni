<?php
declare(strict_types=1);

namespace Oni\Tests\Web;

use Oni\Web\View;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testRenderLoadsIndexLayoutAndContentWithData(): void
    {
        $view = View::init();
        $view->setAttr('paths', [dirname(__DIR__) . '/fixtures/views']);
        $view->setData([
            'title' => 'Oni',
            'body' => 'Framework'
        ]);
        $view->setContentPath('content');

        self::assertSame('index:Oni|layout:content:Framework', $view->render());
    }

    #[RunInSeparateProcess]
    public function testMissingPartialRendersEmptyString(): void
    {
        $view = View::init();
        $view->setAttr('paths', [dirname(__DIR__) . '/fixtures/views']);
        $view->setIndexPath('missing');

        self::assertSame('', $view->render());
    }
}
