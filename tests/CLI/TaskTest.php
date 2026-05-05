<?php

declare(strict_types=1);

namespace Oni\Tests\CLI;

use Oni\CLI\IO;
use Oni\CLI\Task;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for Oni\CLI\Task.
 *
 * Task is abstract; we use a concrete anonymous subclass.
 * The constructor calls IO::init(), so we reset IO's singleton state
 * and set a safe $_SERVER['argv'] before each test.
 */
#[CoversClass(Task::class)]
final class TaskTest extends TestCase
{
    private array $originalArgv = [];

    #[Before]
    public function setUp(): void
    {
        $this->originalArgv = $_SERVER['argv'] ?? [];
        $_SERVER['argv'] = ['script.php'];
        $this->resetIOSingleton();
    }

    #[After]
    public function tearDown(): void
    {
        $_SERVER['argv'] = $this->originalArgv;
        $this->resetIOSingleton();
    }

    private function resetIOSingleton(): void
    {
        $ref = new ReflectionClass(IO::class);
        $prop = $ref->getProperty('_instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private function makeTask(): Task
    {
        return new class extends Task {
            public array $receivedParams = [];

            public function run(array $params = []): void
            {
                $this->receivedParams = $params;
            }
        };
    }

    // -----------------------------------------------------------------------
    // up() / down()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('up() returns true by default')]
    public function testUpReturnsTrueByDefault(): void
    {
        $task = $this->makeTask();

        $this->assertTrue($task->up());
    }

    #[Test]
    #[TestDox('down() runs without error by default')]
    public function testDownRunsWithoutError(): void
    {
        $task = $this->makeTask();
        $task->down();

        $this->addToAssertionCount(1);
    }

    // -----------------------------------------------------------------------
    // run() — abstract method
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('run() receives the params array passed to it')]
    public function testRunReceivesParams(): void
    {
        $task = $this->makeTask();
        $task->run(['a', 'b']);

        $this->assertSame(['a', 'b'], $task->receivedParams);
    }

    #[Test]
    #[TestDox('run() defaults to an empty params array')]
    public function testRunDefaultsToEmptyParams(): void
    {
        $task = $this->makeTask();
        $task->run();

        $this->assertSame([], $task->receivedParams);
    }

    // -----------------------------------------------------------------------
    // Constructor wires io property
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('constructor sets the io property to an IO instance')]
    public function testConstructorWiresIo(): void
    {
        $task = $this->makeTask();

        $ref = new ReflectionClass($task);

        // Walk the inheritance chain to find the 'io' property on Task
        $ioProp = null;
        $current = $ref;
        while ($current !== false) {
            if ($current->hasProperty('io')) {
                $ioProp = $current->getProperty('io');
                break;
            }
            $current = $current->getParentClass();
        }

        $this->assertNotNull($ioProp);
        $ioProp->setAccessible(true);

        $this->assertInstanceOf(IO::class, $ioProp->getValue($task));
    }

    // -----------------------------------------------------------------------
    // setAttr / getAttr (inherited from Basic)
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('setAttr and getAttr work on a Task subclass')]
    public function testSetAndGetAttrWork(): void
    {
        $task = $this->makeTask();
        $task->setAttr('my/key', 'my/value');

        $this->assertSame('my/value', $task->getAttr('my/key'));
    }
}
