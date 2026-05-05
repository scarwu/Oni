<?php

declare(strict_types=1);

namespace Oni\Tests\Web\Controller;

use Oni\Web\Controller\Ajax;
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
 * Tests for Oni\Web\Controller\Ajax.
 */
#[CoversClass(Ajax::class)]
final class AjaxTest extends TestCase
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

    private function makeAjax(): Ajax
    {
        return new class extends Ajax {};
    }

    #[Test]
    #[TestDox('Ajax controller has mode attr set to "ajax"')]
    public function testModeAttrIsAjax(): void
    {
        $ajax = $this->makeAjax();

        $this->assertSame('ajax', $ajax->getAttr('mode'));
    }

    #[Test]
    #[TestDox('up() returns true by default')]
    public function testUpReturnsTrueByDefault(): void
    {
        $this->assertTrue($this->makeAjax()->up());
    }

    #[Test]
    #[TestDox('down() runs without error by default')]
    public function testDownRunsWithoutError(): void
    {
        $this->makeAjax()->down();
        $this->addToAssertionCount(1);
    }

    #[Test]
    #[TestDox('constructor wires req and res properties')]
    public function testConstructorWiresDependencies(): void
    {
        $ajax = $this->makeAjax();
        $ref  = new ReflectionClass($ajax);

        $reqProp = $ref->getParentClass()->getProperty('req');
        $reqProp->setAccessible(true);

        $resProp = $ref->getParentClass()->getProperty('res');
        $resProp->setAccessible(true);

        $this->assertInstanceOf(Req::class, $reqProp->getValue($ajax));
        $this->assertInstanceOf(Res::class, $resProp->getValue($ajax));
    }
}
