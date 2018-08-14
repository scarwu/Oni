<?php
/**
 * Loader
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni;

class Loader
{
    /**
     * @var object
     */
    private static $_instance = null;

    /**
     * @var array
     */
    private static $_namespace_list = [];

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {

        // Namespace Autoload Register
        spl_autoload_register(function ($class_name) {
            $class_name = trim($class_name, '\\');

            foreach (self::$_namespace_list as $namespace => $path_list) {
                $pattern = '/^' . str_replace('\\', '\\\\', $namespace) . '/';

                if (!preg_match($pattern, $class_name)) {
                    continue;
                }

                $class_name = str_replace($namespace, '', trim($class_name, '\\'));
                $class_name = str_replace('\\', '/', trim($class_name, '\\'));

                foreach ($path_list as $path) {
                    if (!file_exists("{$path}/{$class_name}.php")) {
                        continue;
                    }

                    require "{$path}/{$class_name}.php";

                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Append
     *
     * @param string $namespace
     * @param string $path
     *
     * @return bool
     */
    public static function append($namespace, $path)
    {
        if (false === is_string($namespace)
            || false === is_string($path)) {

            return false;
        }

        if (null === self::$_instance) {
            self::$_instance = new self;
        }

        $namespace = trim($namespace, '\\');
        $path = rtrim($path, '/');

        if (!isset(self::$_namespace_list[$namespace])) {
            self::$_namespace_list[$namespace] = [];
        }

        self::$_namespace_list[$namespace][] = $path;

        return true;
    }
}
