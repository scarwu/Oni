<?php

declare(strict_types=1);

namespace Oni\Tests\Web\Helper;

use Oni\Web\Helper\HTML;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(HTML::class)]
final class HTMLTest extends TestCase
{
    // -----------------------------------------------------------------------
    // linkEncode()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('linkEncode() returns the same string when no encoding is needed')]
    public function testLinkEncodePassThroughForSafeString(): void
    {
        $this->assertSame('foo/bar/baz', HTML::linkEncode('foo/bar/baz'));
    }

    #[Test]
    #[TestDox('linkEncode() percent-encodes spaces in path segments')]
    public function testLinkEncodeEncodesSpaces(): void
    {
        $this->assertSame('hello%20world/path', HTML::linkEncode('hello world/path'));
    }

    #[Test]
    #[TestDox('linkEncode() percent-encodes special characters in each segment')]
    #[DataProvider('provideSpecialCharLinks')]
    public function testLinkEncodeSpecialChars(string $input, string $expected): void
    {
        $this->assertSame($expected, HTML::linkEncode($input));
    }

    public static function provideSpecialCharLinks(): array
    {
        return [
            'unicode segment'       => ['café/menu', 'caf%C3%A9/menu'],
            'query-like characters' => ['a?b=c/d', 'a%3Fb%3Dc/d'],
            'hash segment'          => ['page#anchor/sub', 'page%23anchor/sub'],
            'plus sign'             => ['a+b/c', 'a%2Bb/c'],
        ];
    }

    #[Test]
    #[TestDox('linkEncode() preserves the slash separators between segments')]
    public function testLinkEncodePreservesSlashSeparators(): void
    {
        $result = HTML::linkEncode('a/b/c');

        $this->assertSame('a/b/c', $result);
        $this->assertSame(2, substr_count($result, '/'));
    }

    #[Test]
    #[TestDox('linkEncode() handles an empty string without error')]
    public function testLinkEncodeHandlesEmptyString(): void
    {
        $this->assertSame('', HTML::linkEncode(''));
    }

    // -----------------------------------------------------------------------
    // linkTo()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('linkTo() produces a well-formed anchor tag')]
    public function testLinkToProducesAnchorTag(): void
    {
        $result = HTML::linkTo('/home', 'Home');

        $this->assertSame('<a href="/home">Home</a>', $result);
    }

    #[Test]
    #[TestDox('linkTo() URL-encodes the href but leaves the display name as-is')]
    public function testLinkToEncodesHrefButNotName(): void
    {
        $result = HTML::linkTo('/my page/sub', 'My & Page');

        $this->assertSame('<a href="/my%20page/sub">My & Page</a>', $result);
    }

    #[Test]
    #[TestDox('linkTo() encodes unicode characters in the href')]
    public function testLinkToEncodesUnicodeInHref(): void
    {
        $result = HTML::linkTo('/café', 'Cafe');

        $this->assertSame('<a href="/caf%C3%A9">Cafe</a>', $result);
    }

    #[Test]
    #[TestDox('linkTo() with empty link and name produces empty-href anchor')]
    public function testLinkToWithEmptyArguments(): void
    {
        $result = HTML::linkTo('', '');

        $this->assertSame('<a href=""></a>', $result);
    }
}
