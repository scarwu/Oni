<?php
/**
 * HTML
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web\Helper;

final class HTML
{
    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * Link To
     *
     * @param string $link
     * @param string $name
     *
     * @return string
     */
    public static function linkTo($link, $name)
    {
        $link = self::linkEncode($link);

        return "<a href=\"{$link}\">{$name}</a>";
    }

    /**
     * Link Encode
     *
     * @param string $link
     *
     * @return string
     */
    public static function linkEncode($link)
    {
        $segments = explode('/', $link);
        $segments = array_map('rawurlencode', $segments);
        $link = implode('/', $segments);

        return $link;
    }
}
