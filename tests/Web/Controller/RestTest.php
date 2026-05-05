<?php

declare(strict_types=1);

namespace Oni\Tests\Web\Controller;

use Oni\Web\Controller\Rest;
use Oni\Web\Http\Req;
use Oni\Web\Http\Res;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for Oni\Web\Controller\Rest.
 */
#[CoversClass(Rest::class)]
final class RestTest extends TestCase
{
    private array $savedServer = [];

    #[Before]
    public function setUp(): void
    {
        $this->savedServer = $_SERVER;
        $this->resetSingletons();
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    #[After]
    public function tearDown(): void
    {
        $_SERVER = $this->savedServer;
        $this->resetSingletons();
    }

    private function resetSingletons(): void
    {
        foreach ([Req::class, Res::class] as $class) {
            $ref = new ReflectionClass($class);
            $prop = $ref->getProperty('_instance');
            $prop->setAccessible(true);
            $prop->setValue(null, null);
        }
    }

    private function makeRest(): Rest
    {
        return new class extends Rest {};
    }

    #[Test]
    #[TestDox('Rest controller has mode attr set to "rest"')]
    public function testModeAttrIsRest(): void
    {
        $rest = $this->makeRest();

        $this->assertSame('rest', $rest->getAttr('mode'));
    }

    #[Test]
    #[TestDox('up() returns true by default')]
    public function testUpReturnsTrueByDefault(): void
    {
        $this->assertTrue($this->makeRest()->up());
    }

    #[Test]
    #[TestDox('down() runs without error by default')]
    public function testDownRunsWithoutError(): void
    {
        $this->makeRest()->down();
        $this->addToAssertionCount(1);
    }

    #[Test]
    #[TestDox('constructor wires req and res properties')]
    public function testConstructorWiresDependencies(): void
    {
        $rest = $this->makeRest();
        $ref  = new ReflectionClass($rest);

        $reqProp = $ref->getParentClass()->getProperty('req');
        $reqProp->setAccessible(true);

        $resProp = $ref->getParentClass()->getProperty('res');
        $resProp->setAccessible(true);

        $this->assertInstanceOf(Req::class, $reqProp->getValue($rest));
        $this->assertInstanceOf(Res::class, $resProp->getValue($rest));
    }
}
