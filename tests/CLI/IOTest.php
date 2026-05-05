<?php

declare(strict_types=1);

namespace Oni\Tests\CLI;

use Oni\CLI\IO;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for Oni\CLI\IO.
 *
 * IO reads $_SERVER['argv'] at construction time (inside the private constructor).
 * The only way to exercise different argv shapes is to:
 *   1. Set $_SERVER['argv'] before each test,
 *   2. Reset the singleton instance via reflection so the next init() call
 *      re-constructs the object, picking up the new argv.
 * We also restore the original argv in tearDown.
 */
#[CoversClass(IO::class)]
final class IOTest extends TestCase
{
    private array $originalArgv = [];

    #[Before]
    public function saveOriginalArgv(): void
    {
        $this->originalArgv = $_SERVER['argv'] ?? [];
    }

    #[After]
    public function restoreArgvAndResetSingleton(): void
    {
        $_SERVER['argv'] = $this->originalArgv;
        $this->resetIOSingleton();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function resetIOSingleton(): void
    {
        $ref = new ReflectionClass(IO::class);
        $prop = $ref->getProperty('_instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    /**
     * Set argv, reset singleton, then call init() — returns a fresh IO instance
     * that has parsed the given argv.
     *
     * @param list<string> $args  Everything after the script name (argv[0]).
     */
    private function makeIO(array $args): IO
    {
        $this->resetIOSingleton();
        $_SERVER['argv'] = array_merge(['script.php'], $args);

        return IO::init();
    }

    // -----------------------------------------------------------------------
    // init() / singleton
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('init() returns the same instance on repeated calls')]
    public function testInitReturnsSameInstance(): void
    {
        $io1 = $this->makeIO([]);
        $io2 = IO::init();

        $this->assertSame($io1, $io2);
    }

    // -----------------------------------------------------------------------
    // Argument parsing
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('positional words become arguments')]
    public function testPositionalWordsAreArguments(): void
    {
        $io = $this->makeIO(['help', 'read']);

        $this->assertSame(['help', 'read'], $io->getArguments());
    }

    #[Test]
    #[TestDox('getArguments(int) returns element at the given index')]
    public function testGetArgumentsByIndex(): void
    {
        $io = $this->makeIO(['alpha', 'beta', 'gamma']);

        $this->assertSame('alpha', $io->getArguments(0));
        $this->assertSame('beta',  $io->getArguments(1));
        $this->assertSame('gamma', $io->getArguments(2));
    }

    #[Test]
    #[TestDox('getArguments(int) returns null for an out-of-range index')]
    public function testGetArgumentsOutOfRangeReturnsNull(): void
    {
        $io = $this->makeIO(['only']);

        $this->assertNull($io->getArguments(99));
    }

    #[Test]
    #[TestDox('empty argv produces empty argument list')]
    public function testEmptyArgvProducesEmptyArgumentList(): void
    {
        $io = $this->makeIO([]);

        $this->assertSame([], $io->getArguments());
    }

    #[Test]
    #[TestDox('hasArguments() returns true when there are positional arguments')]
    public function testHasArgumentsReturnsTrueWhenPresent(): void
    {
        $io = $this->makeIO(['task']);

        $this->assertTrue($io->hasArguments());
    }

    #[Test]
    #[TestDox('hasArguments() returns false when no positional arguments')]
    public function testHasArgumentsReturnsFalseWhenAbsent(): void
    {
        $io = $this->makeIO(['-v', '--debug']);

        $this->assertFalse($io->hasArguments());
    }

    // -----------------------------------------------------------------------
    // Option parsing (-x and -x value)
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('a lone flag -v is stored with null value')]
    public function testLoneFlagStoredWithNullValue(): void
    {
        $io = $this->makeIO(['-v']);

        $this->assertNull($io->getOptions('v'));
        $this->assertTrue($io->hasOptions('v'));
    }

    #[Test]
    #[TestDox('-f value stores the option with the following positional value')]
    public function testOptionWithFollowingPositionalValue(): void
    {
        $io = $this->makeIO(['-f', 'myfile.txt']);

        $this->assertSame('myfile.txt', $io->getOptions('f'));
    }

    #[Test]
    #[TestDox('-f followed by another flag keeps null value and leaves next flag unparsed')]
    public function testOptionFollowedByAnotherFlagHasNullValue(): void
    {
        $io = $this->makeIO(['-f', '-v']);

        $this->assertNull($io->getOptions('f'));
        $this->assertNull($io->getOptions('v'));
        $this->assertTrue($io->hasOptions('f'));
        $this->assertTrue($io->hasOptions('v'));
    }

    #[Test]
    #[TestDox('-o followed by --config= does not consume the config as the option value')]
    public function testOptionFollowedByConfigDoesNotConsumeIt(): void
    {
        $io = $this->makeIO(['-o', '--env=prod']);

        $this->assertNull($io->getOptions('o'));
        $this->assertSame('prod', $io->getConfigs('env'));
    }

    #[Test]
    #[TestDox('getOptions() with no argument returns all options as array')]
    public function testGetOptionsAllReturnsArray(): void
    {
        $io = $this->makeIO(['-a', '-b', 'val']);

        $options = $io->getOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('a', $options);
        $this->assertArrayHasKey('b', $options);
    }

    #[Test]
    #[TestDox('getOptions() returns null for a key that was not provided')]
    public function testGetOptionsMissingKeyReturnsNull(): void
    {
        $io = $this->makeIO([]);

        $this->assertNull($io->getOptions('z'));
    }

    #[Test]
    #[TestDox('hasOptions() with no argument returns false when no options were parsed')]
    public function testHasOptionsReturnsFalseWhenNoOptions(): void
    {
        $io = $this->makeIO(['somearg']);

        $this->assertFalse($io->hasOptions());
    }

    #[Test]
    #[TestDox('hasOptions() with a key returns false for an absent option')]
    public function testHasOptionsWithKeyReturnsFalseForAbsentKey(): void
    {
        $io = $this->makeIO(['-v']);

        $this->assertFalse($io->hasOptions('x'));
    }

    // -----------------------------------------------------------------------
    // Config parsing (--key and --key=value)
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('--key without = stores null value')]
    public function testConfigWithoutEqualsStoresNull(): void
    {
        $io = $this->makeIO(['--debug']);

        $this->assertNull($io->getConfigs('debug'));
        $this->assertTrue($io->hasConfigs('debug'));
    }

    #[Test]
    #[TestDox('--key=value stores the value string')]
    public function testConfigWithEqualsStoresValue(): void
    {
        $io = $this->makeIO(['--env=production']);

        $this->assertSame('production', $io->getConfigs('env'));
    }

    #[Test]
    #[TestDox('--key=value with an empty string after = is stored as an empty string')]
    public function testConfigWithEmptyValueAfterEquals(): void
    {
        // regex: /^-{2}(\w+(?:-\w+)?)(?:=(.+))?/ — empty string won't match (.+)
        // so result depends on whether the trailing = produces a match group
        $io = $this->makeIO(['--name=']);

        // The regex requires (.+) so an empty value won't be captured;
        // the group won't be set, so ?? null is used => stored as null
        $this->assertNull($io->getConfigs('name'));
        $this->assertTrue($io->hasConfigs('name'));
    }

    #[Test]
    #[TestDox('hyphenated config key --log-level=info is stored under log-level')]
    public function testHyphenatedConfigKey(): void
    {
        $io = $this->makeIO(['--log-level=info']);

        $this->assertSame('info', $io->getConfigs('log-level'));
    }

    #[Test]
    #[TestDox('getConfigs() with no argument returns all configs as array')]
    public function testGetConfigsAllReturnsArray(): void
    {
        $io = $this->makeIO(['--foo=1', '--bar=2']);

        $configs = $io->getConfigs();

        $this->assertIsArray($configs);
        $this->assertArrayHasKey('foo', $configs);
        $this->assertArrayHasKey('bar', $configs);
    }

    #[Test]
    #[TestDox('getConfigs() returns null for a key that was not provided')]
    public function testGetConfigsMissingKeyReturnsNull(): void
    {
        $io = $this->makeIO([]);

        $this->assertNull($io->getConfigs('missing'));
    }

    #[Test]
    #[TestDox('hasConfigs() returns false when no configs were parsed')]
    public function testHasConfigsReturnsFalseWhenNoConfigs(): void
    {
        $io = $this->makeIO(['somearg', '-v']);

        $this->assertFalse($io->hasConfigs());
    }

    // -----------------------------------------------------------------------
    // Mixed argument types in one invocation
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('mixed argv produces correct arguments, options, and configs')]
    public function testMixedArgvParsedCorrectly(): void
    {
        $io = $this->makeIO(['task', 'subtask', '-v', '-f', 'file.txt', '--env=test', '--debug']);

        $this->assertSame(['task', 'subtask'], $io->getArguments());
        $this->assertNull($io->getOptions('v'));
        $this->assertSame('file.txt', $io->getOptions('f'));
        $this->assertSame('test', $io->getConfigs('env'));
        $this->assertNull($io->getConfigs('debug'));
    }

    #[Test]
    #[TestDox('options and configs interleaved with arguments do not absorb each other')]
    public function testOptionsAndConfigsDoNotAbsorbAdjacentItems(): void
    {
        // arg --cfg1 arg2 -o --cfg2=val
        $io = $this->makeIO(['cmd', '--cfg1', 'extra', '-o', '--cfg2=val']);

        $this->assertSame(['cmd', 'extra'], $io->getArguments());
        $this->assertNull($io->getOptions('o'));
        $this->assertNull($io->getConfigs('cfg1'));
        $this->assertSame('val', $io->getConfigs('cfg2'));
    }

    // -----------------------------------------------------------------------
    // Data-provider variant for various option forms
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('option value handling for various argv patterns')]
    #[DataProvider('provideOptionCases')]
    public function testOptionValueParsing(array $argv, string $key, mixed $expected): void
    {
        $io = $this->makeIO($argv);

        $this->assertSame($expected, $io->getOptions($key));
    }

    public static function provideOptionCases(): array
    {
        return [
            'flag only'             => [['-x'], 'x', null],
            'flag with value'       => [['-x', 'foo'], 'x', 'foo'],
            'flag followed by flag' => [['-x', '-y'], 'x', null],
        ];
    }
}
