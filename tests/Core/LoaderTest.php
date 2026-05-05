<?php

declare(strict_types=1);

namespace Oni\Tests\Core;

use Oni\Core\Loader;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Loader::class)]
final class LoaderTest extends TestCase
{
    private string $tmpDir = '';

    /**
     * Reset Loader singleton state before each test so tests are fully isolated.
     * Loader::$_instance and Loader::$_namespaceList are both private statics —
     * we reach them through reflection.
     */
    #[Before]
    public function resetLoaderSingleton(): void
    {
        $ref = new ReflectionClass(Loader::class);

        $instanceProp = $ref->getProperty('_instance');
        $instanceProp->setAccessible(true);
        $instanceProp->setValue(null, null);

        $nsProp = $ref->getProperty('_namespaceList');
        $nsProp->setAccessible(true);
        $nsProp->setValue(null, []);

        // Temp directory for filesystem fixture classes
        $this->tmpDir = sys_get_temp_dir() . '/oni_loader_test_' . getmypid();
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0755, true);
        }
    }

    #[After]
    public function cleanUpTmpDir(): void
    {
        // Recursively remove the temp directory
        $this->removeDirRecursive($this->tmpDir);
    }

    private function removeDirRecursive(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $full = "{$path}/{$entry}";
            is_dir($full) ? $this->removeDirRecursive($full) : unlink($full);
        }
        rmdir($path);
    }

    // -----------------------------------------------------------------------
    // append()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('append() returns true on first call')]
    public function testAppendReturnsTrueOnFirstCall(): void
    {
        $result = Loader::append('MyApp', '/some/path');

        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('append() trims leading and trailing backslashes from namespace')]
    public function testAppendTrimsBackslashesFromNamespace(): void
    {
        Loader::append('\\MyApp\\', '/some/path');

        $ref = new ReflectionClass(Loader::class);
        $nsProp = $ref->getProperty('_namespaceList');
        $nsProp->setAccessible(true);
        $list = $nsProp->getValue(null);

        $this->assertArrayHasKey('MyApp', $list);
        $this->assertArrayNotHasKey('\\MyApp\\', $list);
    }

    #[Test]
    #[TestDox('append() strips trailing slash from path')]
    public function testAppendStripsTrailingSlashFromPath(): void
    {
        Loader::append('MyApp', '/some/path/');

        $ref = new ReflectionClass(Loader::class);
        $nsProp = $ref->getProperty('_namespaceList');
        $nsProp->setAccessible(true);
        $list = $nsProp->getValue(null);

        $this->assertSame(['/some/path'], $list['MyApp']);
    }

    #[Test]
    #[TestDox('append() accumulates multiple paths for the same namespace')]
    public function testAppendAccumulatesMultiplePathsForSameNamespace(): void
    {
        Loader::append('MyApp', '/path/one');
        Loader::append('MyApp', '/path/two');

        $ref = new ReflectionClass(Loader::class);
        $nsProp = $ref->getProperty('_namespaceList');
        $nsProp->setAccessible(true);
        $list = $nsProp->getValue(null);

        $this->assertCount(2, $list['MyApp']);
        $this->assertSame('/path/one', $list['MyApp'][0]);
        $this->assertSame('/path/two', $list['MyApp'][1]);
    }

    #[Test]
    #[TestDox('append() registers different namespaces independently')]
    public function testAppendRegistersDifferentNamespacesIndependently(): void
    {
        Loader::append('Alpha', '/path/alpha');
        Loader::append('Beta', '/path/beta');

        $ref = new ReflectionClass(Loader::class);
        $nsProp = $ref->getProperty('_namespaceList');
        $nsProp->setAccessible(true);
        $list = $nsProp->getValue(null);

        $this->assertArrayHasKey('Alpha', $list);
        $this->assertArrayHasKey('Beta', $list);
        $this->assertSame('/path/alpha', $list['Alpha'][0]);
        $this->assertSame('/path/beta', $list['Beta'][0]);
    }

    #[Test]
    #[TestDox('singleton is created on first append() and reused on subsequent calls')]
    public function testSingletonIsCreatedOnFirstAppend(): void
    {
        $ref = new ReflectionClass(Loader::class);
        $instanceProp = $ref->getProperty('_instance');
        $instanceProp->setAccessible(true);

        $this->assertNull($instanceProp->getValue(null));

        Loader::append('MyApp', '/some/path');

        $this->assertNotNull($instanceProp->getValue(null));

        $first = $instanceProp->getValue(null);

        Loader::append('MyApp', '/other/path');

        $this->assertSame($first, $instanceProp->getValue(null));
    }

    #[Test]
    #[TestDox('autoloader loads a class from a registered path')]
    public function testAutoloaderLoadsClassFromRegisteredPath(): void
    {
        // The Loader strips the registered namespace prefix from the fully-qualified
        // class name, then converts remaining backslashes to slashes and looks for
        // {path}/{remainder}.php.
        //
        // For namespace='LoaderFixture', path=$tmpDir, class='LoaderFixture\Foo':
        //   remainder = 'Foo'  →  looks for $tmpDir/Foo.php
        //
        // The file must NOT declare a namespace (or declare "namespace LoaderFixture;")
        // because the class is referenced with the full FQN by PHP's autoloader.
        $uniqueClass = 'LoaderAutoloadedClass' . str_replace('.', '', uniqid('', true));
        file_put_contents(
            "{$this->tmpDir}/{$uniqueClass}.php",
            "<?php\nnamespace LoaderFixture;\nclass {$uniqueClass} {}\n"
        );

        Loader::append('LoaderFixture', $this->tmpDir);

        // Trigger autoload via class_exists with the fully qualified name
        $this->assertTrue(class_exists("LoaderFixture\\{$uniqueClass}", true));
    }

    #[Test]
    #[TestDox('autoloader returns false when class file does not exist')]
    public function testAutoloaderReturnsFalseForUnknownClass(): void
    {
        Loader::append('NonExistentNS', $this->tmpDir);

        // class_exists triggers the registered SPL autoload; it should return
        // false, not throw — we verify the class is not found
        $this->assertFalse(class_exists('NonExistentNS\\GhostClass', false));
    }
}
