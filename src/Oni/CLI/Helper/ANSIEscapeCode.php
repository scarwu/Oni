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
            $param = implode(self::SEP, $param);
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
        return self::CSI . ($y + 1) . self::SEP . ($x + 1) . 'H';
    }

    /**
     * CUP: Move To
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
     * SGR: Reset
     *
     * @return string
     */
    public static function reset() {
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
    public static function color($text, $fgColor = null, $bgColor = null)
    {
        $startCodes = [];
        $stopCodes = [];

        if (isset(self::$colorMapping[$fgColor]['fg'])) {
            $startCodes[] = self::$colorMapping[$fgColor]['fg'];
            $stopCodes[] = 39;
        }

        if (isset(self::$colorMapping[$bgColor]['bg'])) {
            $startCodes[] = self::$colorMapping[$bgColor]['bg'];
            $stopCodes[] = 49;
        }

        $start = self::SGR($startCodes);
        $end = self::SGR($stopCodes);

        return "{$start}{$text}{$end}";
    }
}
