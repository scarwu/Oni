<?php
declare(strict_types=1);
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
     * @var ?PDO
     */
    private static ?PDO $_instance = null;

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * Initialize
     */
    public static function init(array $config = []): PDO
    {
        if (false === isset(self::$_instance)) {

            // config: host, port, name, user, pass
            $connection = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['name']}", $config['user'], $config['pass']);
            $connection->query("SET NAMES 'utf8'");
            $connection->query("SET CHARACTER_SET_CLIENT=utf8");
            $connection->query("SET CHARACTER_SET_RESULTS=utf8");

            self::$_instance = $connection;
        }

        return self::$_instance;
    }
}
