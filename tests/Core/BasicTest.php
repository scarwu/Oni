<?php
declare(strict_types=1);

namespace Oni\Tests\Core;

use Oni\Core\Basic;
use PHPUnit\Framework\TestCase;

final class BasicTest extends TestCase
{
    public function testSetAttrStoresMixedValues(): void
    {
        $basic = new class extends Basic {};

        self::assertTrue($basic->setAttr('name', 'oni'));
        self::assertSame('oni', $basic->getAttr('name'));

        self::assertTrue($basic->setAttr('payload', ['enabled' => true]));
        self::assertSame(['enabled' => true], $basic->getAttr('payload'));
    }

    public function testGetAttrReturnsNullForMissingKey(): void
    {
        $basic = new class extends Basic {};

        self::assertNull($basic->getAttr('missing'));
    }
}
