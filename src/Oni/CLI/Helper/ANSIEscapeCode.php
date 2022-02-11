<?php
/**
 * ANSI Escape Code
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI\Helper;

final class ANSIEscapeCode
{
    // ANSI Escape Code
    const ESC = "\x1b";
    const CSI = self::ESC . '[';
    const OSC = self::ESC . ']';

    // Control Charater
    const BEL = "\x07";
    const SEP = ';';

    /**
     * @var array
     */
    private static $colorMapping = [
        'black'         => [ 'fg' => '30', 'bg' => '40' ],
        'red'           => [ 'fg' => '31', 'bg' => '41' ],
        'green'         => [ 'fg' => '32', 'bg' => '42' ],
        'yellow'        => [ 'fg' => '33', 'bg' => '43' ],
        'blue'          => [ 'fg' => '34', 'bg' => '44' ],
        'magenta'       => [ 'fg' => '35', 'bg' => '45' ],
        'cyan'          => [ 'fg' => '36', 'bg' => '46' ],
        'white'         => [ 'fg' => '37', 'bg' => '47' ],
        'default'       => [ 'fg' => '39', 'bg' => '49' ],
        'brightBlack'   => [ 'fg' => '90', 'bg' => '100' ],
        'brightRed'     => [ 'fg' => '91', 'bg' => '101' ],
        'brightGreen'   => [ 'fg' => '92', 'bg' => '102' ],
        'brightYellow'  => [ 'fg' => '93', 'bg' => '103' ],
        'brightBlue'    => [ 'fg' => '94', 'bg' => '104' ],
        'brightMagenta' => [ 'fg' => '95', 'bg' => '105' ],
        'brightCyan'    => [ 'fg' => '96', 'bg' => '106' ],
        'brightWhite'   => [ 'fg' => '97', 'bg' => '107' ]
    ];

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * Select Graphic Rendition
     *
     * @param string|array $param
     *
     * @return string
     */
    public static function SGR($param): string
    {
        if (true === is_array($param)) {
            $param = implode(self::SEP, $param);
        }

        return self::CSI . "{$param}m";
    }

    /**
     * Cursor Position
     *
     * @param int $x
     * @param int $y
     *
     * @return string
     */
    public static function CUP(int $x = 0, int $y = 0): string
    {
        return self::CSI . ($y + 1) . self::SEP . ($x + 1) . 'H';
    }

    /**
     * CUP: Move To
     *
     * @param int $x
     * @param int $y
     *
     * @return string
     */
    public static function moveTo(int $x = 0, int $y = 0)
    {
        return self::CUP($x, $y);
    }

    /**
     * SGR: Reset
     *
     * @return string
     */
    public static function reset(): string
    {
        return self::SGR(0);
    }

    /**
     * SGR: Color
     *
     * @param string $text
     * @param string $fgColor
     * @param string $bgColor
     *
     * @return string
     */
    public static function color(string $text, ?string $fgColor = null, ?string $bgColor = null): string
    {
        $startCodes = [];
        $endCodes = [];

        if (true === isset(self::$colorMapping[$fgColor]['fg'])) {
            $startCodes[] = self::$colorMapping[$fgColor]['fg'];
            $endCodes[] = 39;
        }

        if (true === isset(self::$colorMapping[$bgColor]['bg'])) {
            $startCodes[] = self::$colorMapping[$bgColor]['bg'];
            $endCodes[] = 49;
        }

        $start = self::SGR($startCodes);
        $end = self::SGR($endCodes);

        return "{$start}{$text}{$end}";
    }

    /**
     * Cursor Show
     *
     * @return string
     */
    public static function cursorShow(): string
    {
        return self::CSI . '?25h';
    }

    /**
     * Cursor Hide
     *
     * @return string
     */
    public static function cursorHide(): string
    {
        return self::CSI . '?25l';
    }

    /**
     * Cursor Up
     *
     * @param int $n
     *
     * @return string
     */
    public static function cursorUp(int $n = 1): string
    {
        return self::CSI . "{$n}A";
    }

    /**
     * Cursor Down
     *
     * @param int $n
     *
     * @return string
     */
    public static function cursorDown(int $n = 1): string
    {
        return self::CSI . "{$n}B";
    }

    /**
     * Cursor Left
     *
     * @param int $n
     *
     * @return string
     */
    public static function cursorLeft(int $n = 1): string
    {
        return self::CSI . "{$n}C";
    }

    /**
     * Cursor Right
     *
     * @param int $n
     *
     * @return string
     */
    public static function cursorRight(int $n = 1): string
    {
        return self::CSI . "{$n}D";
    }

    /**
     * Cursor Next
     *
     * @param int $n
     *
     * @return string
     */
    public static function cursorNext(int $n = 1): string
    {
        return self::CSI . "{$n}E";
    }

    /**
     * Cursor Prev
     *
     * @param int $n
     *
     * @return string
     */
    public static function cursorPrev(int $n = 1): string
    {
        return self::CSI . "{$n}F";
    }

    /**
     * Cursor Save
     *
     * @return string
     */
    public static function cursorSave(): string
    {
        return self::CSI . (('Apple_Terminal' === getenv('TERM_PROGRAM')) ? '7' : 's');
    }

    /**
     * Cursor Load
     *
     * @return string
     */
    public static function cursorLoad(): string
    {
        return self::CSI . (('Apple_Terminal' === getenv('TERM_PROGRAM')) ? '8' : 'u');
    }
}
