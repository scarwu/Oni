<?php
/**
 * Database Store
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web\Store;

use PDO;
use Oni\Core\Basic;

class Database extends Basic
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
    public static function init($config = []): object
    {
        if (null === self::$_instance) {

            // config: host, port, name, user, pass
            $conn = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}", $config['user'], $config['pass']);
            $conn->query("SET NAMES 'utf8'");
            $conn->query("SET CHARACTER_SET_CLIENT=utf8");
            $conn->query("SET CHARACTER_SET_RESULTS=utf8");

            self::$_instance = $conn;
        }

        return self::$_instance;
    }
}
