<?php

declare(strict_types=1);

namespace Oni\Tests\Core;

use Oni\Core\Basic;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Basic::class)]
final class BasicTest extends TestCase
{
    private Basic $subject;

    protected function setUp(): void
    {
        // Basic is abstract; create a minimal anonymous concrete subclass
        $this->subject = new class extends Basic {};
    }

    // -----------------------------------------------------------------------
    // setAttr
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('setAttr returns true on success')]
    public function testSetAttrReturnsTrue(): void
    {
        $result = $this->subject->setAttr('foo', 'bar');

        $this->assertTrue($result);
    }

    #[Test]
    #[TestDox('setAttr stores a string value retrievable by getAttr')]
    public function testSetAttrStoresStringValue(): void
    {
        $this->subject->setAttr('key', 'value');

        $this->assertSame('value', $this->subject->getAttr('key'));
    }

    #[Test]
    #[TestDox('setAttr stores integer, float, bool, null, and array values')]
    #[DataProvider('provideScalarValues')]
    public function testSetAttrStoresVariousTypes(string $key, mixed $value): void
    {
        $this->subject->setAttr($key, $value);

        $this->assertSame($value, $this->subject->getAttr($key));
    }

    public static function provideScalarValues(): array
    {
        return [
            'integer'       => ['int_key', 42],
            'float'         => ['float_key', 3.14],
            'bool_true'     => ['bool_true_key', true],
            'bool_false'    => ['bool_false_key', false],
            'null'          => ['null_key', null],
            'empty_string'  => ['empty_key', ''],
            'array'         => ['array_key', ['a', 'b', 'c']],
        ];
    }

    #[Test]
    #[TestDox('setAttr overwrites a previously stored value')]
    public function testSetAttrOverwritesPreviousValue(): void
    {
        $this->subject->setAttr('counter', 1);
        $this->subject->setAttr('counter', 99);

        $this->assertSame(99, $this->subject->getAttr('counter'));
    }

    // -----------------------------------------------------------------------
    // getAttr
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('getAttr returns null for a key that was never set')]
    public function testGetAttrReturnsNullForMissingKey(): void
    {
        $result = $this->subject->getAttr('nonexistent');

        $this->assertNull($result);
    }

    #[Test]
    #[TestDox('getAttr treats keys as case-sensitive')]
    public function testGetAttrIsCaseSensitive(): void
    {
        $this->subject->setAttr('MyKey', 'upper');

        $this->assertNull($this->subject->getAttr('mykey'));
        $this->assertSame('upper', $this->subject->getAttr('MyKey'));
    }

    #[Test]
    #[TestDox('getAttr handles slash-separated keys as plain strings (not nested)')]
    public function testGetAttrHandlesSlashSeparatedKeysAsPlainStrings(): void
    {
        $this->subject->setAttr('router/action/default', 'index');

        $this->assertSame('index', $this->subject->getAttr('router/action/default'));
        $this->assertNull($this->subject->getAttr('router/action'));
        $this->assertNull($this->subject->getAttr('router'));
    }

    #[Test]
    #[TestDox('multiple distinct keys do not interfere with each other')]
    public function testMultipleKeysAreIndependent(): void
    {
        $this->subject->setAttr('a', 1);
        $this->subject->setAttr('b', 2);
        $this->subject->setAttr('c', 3);

        $this->assertSame(1, $this->subject->getAttr('a'));
        $this->assertSame(2, $this->subject->getAttr('b'));
        $this->assertSame(3, $this->subject->getAttr('c'));
    }

    #[Test]
    #[TestDox('subclass default _attr values are returned before any setAttr call')]
    public function testSubclassDefaultAttrIsAccessible(): void
    {
        $instance = new class extends Basic {
            protected $_attr = ['mode' => 'page'];
        };

        $this->assertSame('page', $instance->getAttr('mode'));
    }

    #[Test]
    #[TestDox('setAttr on a subclass default key overwrites the default')]
    public function testSetAttrOverwritesSubclassDefault(): void
    {
        $instance = new class extends Basic {
            protected $_attr = ['mode' => 'page'];
        };

        $instance->setAttr('mode', 'rest');

        $this->assertSame('rest', $instance->getAttr('mode'));
    }
}
