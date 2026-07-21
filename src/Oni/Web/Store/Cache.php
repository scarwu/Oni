<?php
declare(strict_types=1);
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
     * @var ?Memcached
     */
    private static ?Memcached $_instance = null;

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * Initialize
     */
    public static function init(array $config = []): Memcached
    {
        if (false === isset(self::$_instance)) {
            self::$_instance = new Memcached();
        }

        return self::$_instance;
    }
}
