<?php
declare(strict_types=1);

namespace Oni\Tests\Core;

use Oni\Core\Loader;
use PHPUnit\Framework\TestCase;
use Tests\Fixtures\Loader\SampleClass;

final class LoaderTest extends TestCase
{
    public function testAppendRegistersNamespaceForAutoload(): void
    {
        $path = dirname(__DIR__) . '/fixtures/loader';

        self::assertTrue(Loader::append('Tests\\Fixtures\\Loader', $path));
        self::assertSame('loaded', SampleClass::value());
    }
}
