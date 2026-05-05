<?php

declare(strict_types=1);

namespace Oni\Tests\Web\Http;

use Oni\Web\Http\Req;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for Oni\Web\Http\Req.
 *
 * Req reads superglobals directly on each method call, so we can mutate
 * $_SERVER between method calls on the same (singleton) instance.
 * We save/restore $_SERVER and reset the singleton before/after each test.
 */
#[CoversClass(Req::class)]
final class ReqTest extends TestCase
{
    private array $savedServer = [];

    #[Before]
    public function saveServerAndResetSingleton(): void
    {
        $this->savedServer = $_SERVER;
        $this->resetSingleton();
    }

    #[After]
    public function restoreServerAndResetSingleton(): void
    {
        $_SERVER = $this->savedServer;
        $this->resetSingleton();
    }

    private function resetSingleton(): void
    {
        $ref = new ReflectionClass(Req::class);
        $prop = $ref->getProperty('_instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private function freshReq(): Req
    {
        $this->resetSingleton();

        return Req::init();
    }

    // -----------------------------------------------------------------------
    // init() singleton
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('init() returns the same instance on repeated calls')]
    public function testInitReturnsSameInstance(): void
    {
        $r1 = $this->freshReq();
        $r2 = Req::init();

        $this->assertSame($r1, $r2);
    }

    // -----------------------------------------------------------------------
    // method()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('method() returns lowercase REQUEST_METHOD')]
    #[DataProvider('provideRequestMethods')]
    public function testMethodReturnsLowercase(string $raw, string $expected): void
    {
        $_SERVER['REQUEST_METHOD'] = $raw;
        unset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);

        $this->assertSame($expected, $this->freshReq()->method());
    }

    public static function provideRequestMethods(): array
    {
        return [
            'GET'    => ['GET', 'get'],
            'POST'   => ['POST', 'post'],
            'PUT'    => ['PUT', 'put'],
            'DELETE' => ['DELETE', 'delete'],
            'PATCH'  => ['PATCH', 'patch'],
        ];
    }

    #[Test]
    #[TestDox('method() honours HTTP_X_HTTP_METHOD_OVERRIDE when present')]
    public function testMethodHonoursOverrideHeader(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'DELETE';

        $this->assertSame('delete', $this->freshReq()->method());
    }

    // -----------------------------------------------------------------------
    // contentLength()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('contentLength() returns integer when CONTENT_LENGTH is set')]
    public function testContentLengthReturnsInteger(): void
    {
        $_SERVER['CONTENT_LENGTH'] = '512';

        $this->assertSame(512, $this->freshReq()->contentLength());
    }

    #[Test]
    #[TestDox('contentLength() returns 0 when CONTENT_LENGTH is absent')]
    public function testContentLengthDefaultsToZero(): void
    {
        unset($_SERVER['CONTENT_LENGTH']);

        $this->assertSame(0, $this->freshReq()->contentLength());
    }

    #[Test]
    #[TestDox('contentLength() returns 0 when CONTENT_LENGTH is empty string')]
    public function testContentLengthEmptyStringReturnsZero(): void
    {
        $_SERVER['CONTENT_LENGTH'] = '';

        $this->assertSame(0, $this->freshReq()->contentLength());
    }

    // -----------------------------------------------------------------------
    // contentType()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('contentType() returns the MIME type without parameters')]
    public function testContentTypeStripsParameters(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'multipart/form-data; boundary=----Boundary';

        $this->assertSame('multipart/form-data', $this->freshReq()->contentType());
    }

    #[Test]
    #[TestDox('contentType() returns null when CONTENT_TYPE is absent')]
    public function testContentTypeNullWhenAbsent(): void
    {
        unset($_SERVER['CONTENT_TYPE']);

        $this->assertNull($this->freshReq()->contentType());
    }

    #[Test]
    #[TestDox('contentType() returns null when CONTENT_TYPE is empty string')]
    public function testContentTypeNullWhenEmpty(): void
    {
        $_SERVER['CONTENT_TYPE'] = '';

        $this->assertNull($this->freshReq()->contentType());
    }

    // -----------------------------------------------------------------------
    // uri()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('uri() trims leading and trailing slashes from PATH_INFO')]
    public function testUriTrimsSlashesFromPathInfo(): void
    {
        $_SERVER['PATH_INFO'] = '/about/us/';
        unset($_SERVER['REQUEST_URI']);

        $this->assertSame('about/us', $this->freshReq()->uri());
    }

    #[Test]
    #[TestDox('uri() falls back to REQUEST_URI when PATH_INFO is absent')]
    public function testUriFallsBackToRequestUri(): void
    {
        unset($_SERVER['PATH_INFO']);
        $_SERVER['REQUEST_URI'] = '/products/detail?id=42';

        $this->assertSame('products/detail', $this->freshReq()->uri());
    }

    #[Test]
    #[TestDox('uri() returns empty string for root /')]
    public function testUriRootReturnsEmptyString(): void
    {
        $_SERVER['PATH_INFO'] = '/';

        $this->assertSame('', $this->freshReq()->uri());
    }

    // -----------------------------------------------------------------------
    // isAjax()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('isAjax() returns true when X-Requested-With is XMLHttpRequest')]
    public function testIsAjaxReturnsTrueWhenHeaderPresent(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->assertTrue($this->freshReq()->isAjax());
    }

    #[Test]
    #[TestDox('isAjax() returns false when the header is absent')]
    public function testIsAjaxReturnsFalseWhenAbsent(): void
    {
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);

        $this->assertFalse($this->freshReq()->isAjax());
    }

    #[Test]
    #[TestDox('isAjax() returns false when the header has an unexpected value')]
    public function testIsAjaxReturnsFalseForOtherHeaderValue(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'fetch';

        $this->assertFalse($this->freshReq()->isAjax());
    }

    // -----------------------------------------------------------------------
    // query()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('query() returns the current $_GET superglobal')]
    public function testQueryReturnsGetSuperglobal(): void
    {
        $_GET = ['page' => '2', 'sort' => 'name'];

        $this->assertSame(['page' => '2', 'sort' => 'name'], $this->freshReq()->query());
    }

    // -----------------------------------------------------------------------
    // protocol() / scheme() / host()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('protocol() returns SERVER_PROTOCOL in lowercase')]
    public function testProtocolIsLowercase(): void
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $this->assertSame('http/1.1', $this->freshReq()->protocol());
    }

    #[Test]
    #[TestDox('scheme() returns REQUEST_SCHEME in lowercase')]
    public function testSchemeIsLowercase(): void
    {
        $_SERVER['REQUEST_SCHEME'] = 'HTTPS';

        $this->assertSame('https', $this->freshReq()->scheme());
    }

    #[Test]
    #[TestDox('host() returns HTTP_HOST verbatim')]
    public function testHostReturnsVerbatim(): void
    {
        $_SERVER['HTTP_HOST'] = 'example.com';

        $this->assertSame('example.com', $this->freshReq()->host());
    }
}
