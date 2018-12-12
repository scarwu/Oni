<?php
/**
 * ANSI Escape Code
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI\IO;

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
    private static $fgColorMapping = [
        'black'         => '30',
        'red'           => '31',
        'green'         => '32',
        'yellow'        => '33',
        'blue'          => '34',
        'magenta'       => '35',
        'cyan'          => '36',
        'white'         => '37',
        'default'       => '39',
        'brightBlack'   => '90',
        'brightRed'     => '91',
        'brightGreen'   => '92',
        'brightYellow'  => '93',
        'brightBlue'    => '94',
        'brightMagenta' => '95',
        'brightCyan'    => '96',
        'brightWhite'   => '97'
    ];

    /**
     * @var array
     */
    private static $bgColorMapping = [
        'black'         => '40',
        'red'           => '41',
        'green'         => '42',
        'yellow'        => '43',
        'blue'          => '44',
        'magenta'       => '45',
        'cyan'          => '46',
        'white'         => '47',
        'default'       => '49',
        'brightBlack'   => '100',
        'brightRed'     => '101',
        'brightGreen'   => '102',
        'brightYellow'  => '103',
        'brightBlue'    => '104',
        'brightMagenta' => '105',
        'brightCyan'    => '106',
        'brightWhite'   => '107'
    ];

    private function __construct() {}

    /**
     * Select Graphic Rendition
     *
     * @param string|array $param
     *
     * @return string
     */
    public static function SGR($param) {
        if (is_array($param)) {
            $param = implode(SEP, $param);
        }

        return self::CSI . "{$param}m";
    }

    /**
     * Cursor Position
     *
     * @param string $x
     * @param string $y
     *
     * @return string
     */
    public static function CUP($x, $y) {
        return self::CSI . ($y + 1) . SEP . ($x + 1) . 'H';
    }

    /**
     * Move To
     *
     * @param string $x
     * @param string $y
     *
     * @return string
     */
    public static function moveTo($x, $y) {
        return self::CUP($x, $y);
    }

    /**
     * Reset
     *
     * @return string
     */
    public static function reset() {
        return self::SGR(0);
    }

    /**
     * Front Ground Color
     *
     * @param string $text
     * @param string $fgColor
     *
     * @return string
     */
    public static function color($text, $fgColor = null, $bgColor = null)
    {
        $startCodes = [];
        $stopCodes = [];

        if (isset(self::$fgColorMapping[$fgColor])) {
            $startCodes[] = self::$fgColorMapping[$fgColor];
            $stopCodes[] = 39;
        }

        if (isset(self::$bgColorMapping[$bgColor])) {
            $startCodes[] = self::$bgColorMapping[$bgColor];
            $stopCodes[] = 49;
        }

        $start = self::SGR($startCodes);
        $end = self::SGR($stopCodes);

        return "{$start}{$text}{$end}";
    }
}
