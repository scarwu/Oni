<?php

declare(strict_types=1);

namespace Oni\Tests\CLI;

use Oni\CLI\App;
use Oni\CLI\IO;
use Oni\Core\Loader;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for Oni\CLI\App.
 *
 * We exercise the public run() method and the attr-based configuration.
 * Task files live in tests/fixtures/tasks/ and are loaded by the real Loader.
 * We reset IO and Loader singletons around each test.
 */
#[CoversClass(App::class)]
final class AppTest extends TestCase
{
    private array $originalArgv = [];
    private string $taskFixturesPath = '';

    #[Before]
    public function setUp(): void
    {
        $this->originalArgv = $_SERVER['argv'] ?? [];
        $this->taskFixturesPath = dirname(__DIR__) . '/fixtures/tasks';

        $this->resetSingletons();
    }

    #[After]
    public function tearDown(): void
    {
        $_SERVER['argv'] = $this->originalArgv;
        $this->resetSingletons();
    }

    private function resetSingletons(): void
    {
        // Reset IO
        $refIO = new ReflectionClass(IO::class);
        $propIO = $refIO->getProperty('_instance');
        $propIO->setAccessible(true);
        $propIO->setValue(null, null);

        // Reset Loader
        $refLoader = new ReflectionClass(Loader::class);

        $propInst = $refLoader->getProperty('_instance');
        $propInst->setAccessible(true);
        $propInst->setValue(null, null);

        $propNs = $refLoader->getProperty('_namespaceList');
        $propNs->setAccessible(true);
        $propNs->setValue(null, []);
    }

    private function makeApp(array $argv = []): App
    {
        $_SERVER['argv'] = array_merge(['script.php'], $argv);

        // Fresh IO must be constructed AFTER setting argv
        $refIO = new ReflectionClass(IO::class);
        $propIO = $refIO->getProperty('_instance');
        $propIO->setAccessible(true);
        $propIO->setValue(null, null);

        return new App();
    }

    // -----------------------------------------------------------------------
    // Default attrs
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('default router/task/default attr is "main"')]
    public function testDefaultTaskAttrIsMain(): void
    {
        $app = $this->makeApp([]);

        $this->assertSame('main', $app->getAttr('router/task/default'));
    }

    #[Test]
    #[TestDox('task/namespace and task/path default to null')]
    public function testDefaultNamespaceAndPathAreNull(): void
    {
        $app = $this->makeApp([]);

        $this->assertNull($app->getAttr('task/namespace'));
        $this->assertNull($app->getAttr('task/path'));
    }

    // -----------------------------------------------------------------------
    // run() without task configuration
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('run() returns false when task/namespace and task/path are not set')]
    public function testRunReturnsFalseWithoutNamespaceAndPath(): void
    {
        $app = $this->makeApp([]);

        $result = $app->run();

        $this->assertFalse($result);
    }

    // -----------------------------------------------------------------------
    // run() with router/event/up returning false
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('run() returns false when router/event/up callable returns false')]
    public function testRunReturnsFalseWhenUpEventReturnsFalse(): void
    {
        $app = $this->makeApp([]);
        $app->setAttr('router/event/up', fn() => false);
        $app->setAttr('task/namespace', 'Oni\\Tests\\Fixture\\Task');
        $app->setAttr('task/path', $this->taskFixturesPath);

        $result = $app->run();

        $this->assertFalse($result);
    }

    // -----------------------------------------------------------------------
    // run() dispatches to MainTask via default route
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('run() returns true and dispatches to the default MainTask when argv is empty')]
    public function testRunDispatchesToDefaultMainTask(): void
    {
        // Register the fixture task namespace BEFORE creating the App
        // so the autoloader can find it
        Loader::append('Oni\\Tests\\Fixture\\Task', $this->taskFixturesPath);

        $app = $this->makeApp([]); // no positional argv
        $app->setAttr('task/namespace', 'Oni\\Tests\\Fixture\\Task');
        $app->setAttr('task/path', $this->taskFixturesPath);

        $result = $app->run();

        $this->assertTrue($result);
    }

    // -----------------------------------------------------------------------
    // run() with router/event/down callable
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('down event is called after successful task dispatch')]
    public function testDownEventCalledAfterSuccessfulDispatch(): void
    {
        Loader::append('Oni\\Tests\\Fixture\\Task', $this->taskFixturesPath);

        $downCalled = false;

        $app = $this->makeApp([]);
        $app->setAttr('task/namespace', 'Oni\\Tests\\Fixture\\Task');
        $app->setAttr('task/path', $this->taskFixturesPath);
        $app->setAttr('router/event/down', function () use (&$downCalled): void {
            $downCalled = true;
        });

        $app->run();

        $this->assertTrue($downCalled);
    }

    // -----------------------------------------------------------------------
    // setAttr / getAttr passthrough
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('setAttr and getAttr work for arbitrary keys on App')]
    public function testSetAndGetAttr(): void
    {
        $app = $this->makeApp([]);
        $app->setAttr('task/namespace', 'App\\Task');

        $this->assertSame('App\\Task', $app->getAttr('task/namespace'));
    }
}
