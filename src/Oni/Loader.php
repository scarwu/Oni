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
    private static $_namespaceList = [];

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct()
    {
        // Namespace Autoload Register
        spl_autoload_register(function ($className) {
            $className = trim($className, '\\');

            foreach (self::$_namespaceList as $namespace => $pathList) {
                $pattern = '/^' . str_replace('\\', '\\\\', $namespace) . '/';

                if (!preg_match($pattern, $className)) {
                    continue;
                }

                $className = str_replace($namespace, '', trim($className, '\\'));
                $className = str_replace('\\', '/', trim($className, '\\'));

                foreach ($pathList as $path) {
                    if (!file_exists("{$path}/{$className}.php")) {
                        continue;
                    }

                    require "{$path}/{$className}.php";

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

        if (!isset(self::$_namespaceList[$namespace])) {
            self::$_namespaceList[$namespace] = [];
        }

        self::$_namespaceList[$namespace][] = $path;

        return true;
    }
}
