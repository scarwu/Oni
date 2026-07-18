<?php
declare(strict_types=1);

namespace Oni\Tests\Web\Helper;

use Oni\Web\Helper\HTML;
use PHPUnit\Framework\TestCase;

final class HTMLTest extends TestCase
{
    public function testLinkEncodeEncodesEachPathSegment(): void
    {
        self::assertSame('/about/oni%20framework', HTML::linkEncode('/about/oni framework'));
    }

    public function testLinkToBuildsEncodedAnchor(): void
    {
        self::assertSame(
            '<a href="/about/oni%20framework">About</a>',
            HTML::linkTo('/about/oni framework', 'About')
        );
    }
}
