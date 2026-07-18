<?php
declare(strict_types=1);

namespace Oni\Tests\CLI;

use Oni\CLI\IO;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class IOTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testParsesArgumentsOptionsAndConfigs(): void
    {
        $_SERVER['argv'] = [
            'boot.php',
            'Task',
            'first',
            '-ab',
            'value',
            '-x',
            '--name=oni',
            '--flag'
        ];

        $io = IO::init();

        self::assertSame(['Task', 'first'], $io->getArguments());
        self::assertSame('Task', $io->getArguments(0));
        self::assertSame('first', $io->getArguments(1));
        self::assertNull($io->getArguments(2));

        self::assertSame(['ab' => 'value', 'x' => null], $io->getOptions());
        self::assertTrue($io->hasOptions());
        self::assertTrue($io->hasOptions('ab'));
        self::assertSame('value', $io->getOptions('ab'));
        self::assertNull($io->getOptions('missing'));

        self::assertSame(['name' => 'oni', 'flag' => null], $io->getConfigs());
        self::assertTrue($io->hasConfigs());
        self::assertTrue($io->hasConfigs('flag'));
        self::assertSame('oni', $io->getConfigs('name'));
        self::assertNull($io->getConfigs('missing'));
    }

    #[RunInSeparateProcess]
    public function testEmptyInputHasNoArgumentsOptionsOrConfigs(): void
    {
        $_SERVER['argv'] = ['boot.php'];

        $io = IO::init();

        self::assertFalse($io->hasArguments());
        self::assertFalse($io->hasOptions());
        self::assertFalse($io->hasConfigs());
        self::assertSame([], $io->getArguments());
        self::assertSame([], $io->getOptions());
        self::assertSame([], $io->getConfigs());
    }
}
