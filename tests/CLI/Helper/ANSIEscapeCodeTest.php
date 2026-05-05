<?php

declare(strict_types=1);

namespace Oni\Tests\CLI\Helper;

use Oni\CLI\Helper\ANSIEscapeCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ANSIEscapeCode::class)]
final class ANSIEscapeCodeTest extends TestCase
{
    // Escape sequence constants from the class under test
    private const ESC = "\x1b";
    private const CSI = "\x1b[";

    // -----------------------------------------------------------------------
    // Constants
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('ESC constant equals ASCII 0x1B')]
    public function testEscConstant(): void
    {
        $this->assertSame("\x1b", ANSIEscapeCode::ESC);
    }

    #[Test]
    #[TestDox('CSI constant equals ESC followed by [')]
    public function testCsiConstant(): void
    {
        $this->assertSame("\x1b[", ANSIEscapeCode::CSI);
    }

    #[Test]
    #[TestDox('KEY_CODE_ENTER is 10 (LF)')]
    public function testKeyCodeEnter(): void
    {
        $this->assertSame(10, ANSIEscapeCode::KEY_CODE_ENTER);
    }

    // -----------------------------------------------------------------------
    // SGR()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('SGR() with a string parameter wraps it in CSI...m')]
    public function testSgrWithStringParam(): void
    {
        $result = ANSIEscapeCode::SGR('1');

        $this->assertSame(self::CSI . '1m', $result);
    }

    #[Test]
    #[TestDox('SGR() with an array joins elements with semicolons')]
    public function testSgrWithArrayParam(): void
    {
        $result = ANSIEscapeCode::SGR(['31', '42']);

        $this->assertSame(self::CSI . '31;42m', $result);
    }

    #[Test]
    #[TestDox('SGR() with empty array produces CSI ;m (degenerate but defined)')]
    public function testSgrWithEmptyArray(): void
    {
        $result = ANSIEscapeCode::SGR([]);

        $this->assertSame(self::CSI . 'm', $result);
    }

    // -----------------------------------------------------------------------
    // reset()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('reset() returns SGR(0)')]
    public function testReset(): void
    {
        $this->assertSame(self::CSI . '0m', ANSIEscapeCode::reset());
    }

    // -----------------------------------------------------------------------
    // CUP() / moveTo()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('CUP(0,0) returns cursor position sequence for row 1 col 1')]
    public function testCupOrigin(): void
    {
        $this->assertSame(self::CSI . '1;1H', ANSIEscapeCode::CUP(0, 0));
    }

    #[Test]
    #[TestDox('CUP(x,y) offsets are 1-based (adds 1 to each parameter)')]
    public function testCupOffsetsAreOneBased(): void
    {
        $this->assertSame(self::CSI . '4;3H', ANSIEscapeCode::CUP(2, 3));
    }

    #[Test]
    #[TestDox('moveTo() delegates to CUP() with the same arguments')]
    public function testMoveToMatchesCup(): void
    {
        $this->assertSame(ANSIEscapeCode::CUP(5, 7), ANSIEscapeCode::moveTo(5, 7));
    }

    // -----------------------------------------------------------------------
    // color()
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('color() wraps text with foreground SGR codes when only fgColor supplied')]
    public function testColorWithForegroundOnly(): void
    {
        $result = ANSIEscapeCode::color('hello', 'red');

        // Expect: CSI 31m hello CSI 39m
        $this->assertSame(self::CSI . '31m' . 'hello' . self::CSI . '39m', $result);
    }

    #[Test]
    #[TestDox('color() wraps text with background SGR codes when only bgColor supplied')]
    public function testColorWithBackgroundOnly(): void
    {
        $result = ANSIEscapeCode::color('text', null, 'blue');

        // Expect: CSI 44m text CSI 49m
        $this->assertSame(self::CSI . '44m' . 'text' . self::CSI . '49m', $result);
    }

    #[Test]
    #[TestDox('color() combines fg and bg codes in the opening sequence')]
    public function testColorWithBothForegroundAndBackground(): void
    {
        $result = ANSIEscapeCode::color('hi', 'green', 'yellow');

        // Expect: CSI 32;43m hi CSI 39;49m
        $this->assertSame(self::CSI . '32;43m' . 'hi' . self::CSI . '39;49m', $result);
    }

    #[Test]
    #[TestDox('color() with unknown color names produces empty SGR sequences')]
    public function testColorWithUnknownColorNamesProducesEmptySGR(): void
    {
        $result = ANSIEscapeCode::color('text', 'neonpink');

        // No codes in the mapping — start/end are both CSI m (empty param list)
        $this->assertSame(self::CSI . 'm' . 'text' . self::CSI . 'm', $result);
    }

    #[Test]
    #[TestDox('color() with null fg and null bg still returns wrapped text')]
    public function testColorWithBothNullReturnsWrappedText(): void
    {
        $result = ANSIEscapeCode::color('plain', null, null);

        $this->assertSame(self::CSI . 'm' . 'plain' . self::CSI . 'm', $result);
    }

    #[Test]
    #[TestDox('color() supports brightBlack foreground')]
    public function testColorBrightBlackForeground(): void
    {
        $result = ANSIEscapeCode::color('dim', 'brightBlack');

        $this->assertSame(self::CSI . '90m' . 'dim' . self::CSI . '39m', $result);
    }

    // -----------------------------------------------------------------------
    // Cursor visibility
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('cursorShow() returns CSI ?25h')]
    public function testCursorShow(): void
    {
        $this->assertSame(self::CSI . '?25h', ANSIEscapeCode::cursorShow());
    }

    #[Test]
    #[TestDox('cursorHide() returns CSI ?25l')]
    public function testCursorHide(): void
    {
        $this->assertSame(self::CSI . '?25l', ANSIEscapeCode::cursorHide());
    }

    // -----------------------------------------------------------------------
    // Cursor movement
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('cursorUp(n) produces CSI nA')]
    public function testCursorUp(): void
    {
        $this->assertSame(self::CSI . '3A', ANSIEscapeCode::cursorUp(3));
    }

    #[Test]
    #[TestDox('cursorDown(n) produces CSI nB')]
    public function testCursorDown(): void
    {
        $this->assertSame(self::CSI . '2B', ANSIEscapeCode::cursorDown(2));
    }

    #[Test]
    #[TestDox('cursorLeft(n) produces CSI nC')]
    public function testCursorLeft(): void
    {
        $this->assertSame(self::CSI . '1C', ANSIEscapeCode::cursorLeft(1));
    }

    #[Test]
    #[TestDox('cursorRight(n) produces CSI nD')]
    public function testCursorRight(): void
    {
        $this->assertSame(self::CSI . '4D', ANSIEscapeCode::cursorRight(4));
    }

    #[Test]
    #[TestDox('cursorNext(n) produces CSI nE')]
    public function testCursorNext(): void
    {
        $this->assertSame(self::CSI . '5E', ANSIEscapeCode::cursorNext(5));
    }

    #[Test]
    #[TestDox('cursorPrev(n) produces CSI nF')]
    public function testCursorPrev(): void
    {
        $this->assertSame(self::CSI . '2F', ANSIEscapeCode::cursorPrev(2));
    }

    #[Test]
    #[TestDox('cursor movement methods default to n=1')]
    public function testCursorMovementDefaultsToOne(): void
    {
        $this->assertSame(self::CSI . '1A', ANSIEscapeCode::cursorUp());
        $this->assertSame(self::CSI . '1B', ANSIEscapeCode::cursorDown());
        $this->assertSame(self::CSI . '1C', ANSIEscapeCode::cursorLeft());
        $this->assertSame(self::CSI . '1D', ANSIEscapeCode::cursorRight());
        $this->assertSame(self::CSI . '1E', ANSIEscapeCode::cursorNext());
        $this->assertSame(self::CSI . '1F', ANSIEscapeCode::cursorPrev());
    }

    // -----------------------------------------------------------------------
    // cursorSave / cursorLoad (terminal-specific branch)
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('cursorSave() returns a non-empty ANSI sequence')]
    public function testCursorSaveReturnsNonEmptySequence(): void
    {
        $result = ANSIEscapeCode::cursorSave();

        $this->assertStringStartsWith(self::CSI, $result);
        $this->assertNotSame('', $result);
    }

    #[Test]
    #[TestDox('cursorLoad() returns a non-empty ANSI sequence')]
    public function testCursorLoadReturnsNonEmptySequence(): void
    {
        $result = ANSIEscapeCode::cursorLoad();

        $this->assertStringStartsWith(self::CSI, $result);
        $this->assertNotSame('', $result);
    }

    // -----------------------------------------------------------------------
    // All named colors round-trip
    // -----------------------------------------------------------------------

    #[Test]
    #[TestDox('every named color in the mapping produces a valid fg/bg sequence')]
    #[DataProvider('provideNamedColors')]
    public function testEveryNamedColorProducesASequence(string $name, string $expectedFg, string $expectedBg): void
    {
        $fg = ANSIEscapeCode::color('x', $name);
        $bg = ANSIEscapeCode::color('x', null, $name);

        $this->assertStringContainsString($expectedFg, $fg);
        $this->assertStringContainsString($expectedBg, $bg);
    }

    public static function provideNamedColors(): array
    {
        return [
            'black'         => ['black',         '30', '40'],
            'red'           => ['red',           '31', '41'],
            'green'         => ['green',         '32', '42'],
            'yellow'        => ['yellow',        '33', '43'],
            'blue'          => ['blue',          '34', '44'],
            'magenta'       => ['magenta',       '35', '45'],
            'cyan'          => ['cyan',          '36', '46'],
            'white'         => ['white',         '37', '47'],
            'default'       => ['default',       '39', '49'],
            'brightBlack'   => ['brightBlack',   '90', '100'],
            'brightRed'     => ['brightRed',     '91', '101'],
            'brightGreen'   => ['brightGreen',   '92', '102'],
            'brightYellow'  => ['brightYellow',  '93', '103'],
            'brightBlue'    => ['brightBlue',    '94', '104'],
            'brightMagenta' => ['brightMagenta', '95', '105'],
            'brightCyan'    => ['brightCyan',    '96', '106'],
            'brightWhite'   => ['brightWhite',   '97', '107'],
        ];
    }
}
