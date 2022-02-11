<?php
/**
 * Cache Store
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web\Store;

use Memcached;
use Oni\Core\Basic;

class Cache extends Basic
{
    /**
     * @var object
     */
    private static $_instance = null;

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * Initialize
     */
    public static function init($config = null): object
    {
        if (null === self::$_instance) {
            self::$_instance = Memcached();
        }

        return self::$_instance;
    }
}
