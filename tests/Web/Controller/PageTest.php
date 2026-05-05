<?php

declare(strict_types=1);

namespace Oni\Tests\Web\Controller;

use Oni\Web\Controller\Page;
use Oni\Web\Http\Req;
use Oni\Web\Http\Res;
use Oni\Web\View;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for Oni\Web\Controller\Page.
 *
 * Page is abstract; we instantiate a concrete anonymous subclass.
 * The constructor calls Req::init(), Res::init(), and View::init(), so we
 * reset all three singletons before and after each test.
 */
#[CoversClass(Page::class)]
final class PageTest extends TestCase
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
        foreach ([Req::class, Res::class, View::class] as $class) {
            $ref = new ReflectionClass($class);
            $prop = $ref->getProperty('_instance');
            $prop->setAccessible(true);
            $prop->setValue(null, null);
        }
    }

    private function makePage(): Page
    {
        return new class extends Page {
            // Concrete subclass — no additional logic needed
        };
    }

    // -----------------------------------------------------------------------
    // mode attribute
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('Page controller has mode attr set to "page"')]
    public function testModeAttrIsPage(): void
    {
        $page = $this->makePage();

        $this->assertSame('page', $page->getAttr('mode'));
    }

    // -----------------------------------------------------------------------
    // up() / down()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('up() returns true by default')]
    public function testUpReturnsTrueByDefault(): void
    {
        $page = $this->makePage();

        $this->assertTrue($page->up());
    }

    #[Test]
    #[TestDox('down() runs without error by default')]
    public function testDownRunsWithoutError(): void
    {
        $page = $this->makePage();

        // down() has void return type; we just ensure no exception is thrown
        $page->down();
        $this->addToAssertionCount(1);
    }

    // -----------------------------------------------------------------------
    // Protected dependencies wired through constructor
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('constructor wires req, res, and view properties')]
    public function testConstructorWiresDependencies(): void
    {
        $page = $this->makePage();

        $ref = new ReflectionClass($page);

        $reqProp = $ref->getParentClass()->getProperty('req');
        $reqProp->setAccessible(true);

        $resProp = $ref->getParentClass()->getProperty('res');
        $resProp->setAccessible(true);

        $viewProp = $ref->getParentClass()->getProperty('view');
        $viewProp->setAccessible(true);

        $this->assertInstanceOf(Req::class, $reqProp->getValue($page));
        $this->assertInstanceOf(Res::class, $resProp->getValue($page));
        $this->assertInstanceOf(View::class, $viewProp->getValue($page));
    }
}
