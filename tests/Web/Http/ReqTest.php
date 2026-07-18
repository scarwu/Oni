<?php
declare(strict_types=1);

namespace Oni\Tests\Web\Http;

use Oni\Web\Http\Req;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class ReqTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testReadsRequestValuesFromGlobals(): void
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_HTTP_METHOD_OVERRIDE' => 'PUT',
            'CONTENT_LENGTH' => '12',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_SCHEME' => 'HTTPS',
            'HTTP_HOST' => 'oni.test',
            'REQUEST_URI' => '/about/oni?debug=1',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
        ];
        $_GET = ['debug' => '1'];
        $_POST = ['name' => 'oni'];
        $_FILES = ['avatar' => ['name' => 'oni.png']];

        $req = Req::init();

        self::assertSame('put', $req->method());
        self::assertSame(12, $req->contentLength());
        self::assertSame('application/x-www-form-urlencoded', $req->contentType());
        self::assertSame('http/1.1', $req->protocol());
        self::assertSame('https', $req->scheme());
        self::assertSame('oni.test', $req->host());
        self::assertSame('about/oni', $req->uri());
        self::assertSame(['debug' => '1'], $req->query());
        self::assertSame(['name' => 'oni'], $req->content());
        self::assertSame(['avatar' => ['name' => 'oni.png']], $req->file());
        self::assertTrue($req->isAjax());
    }

    #[RunInSeparateProcess]
    public function testPathInfoTakesPrecedenceForUri(): void
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET',
            'PATH_INFO' => '/api/rest',
            'REQUEST_URI' => '/fallback'
        ];

        self::assertSame('api/rest', Req::init()->uri());
    }

    #[RunInSeparateProcess]
    public function testSchemeDefaultsToHttpWithoutRequestScheme(): void
    {
        $_SERVER = [
            'REQUEST_METHOD' => 'GET'
        ];

        self::assertSame('http', Req::init()->scheme());
    }
}
