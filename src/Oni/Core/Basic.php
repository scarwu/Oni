<?php
/**
 * Basic
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Core;

abstract class Basic
{
    /**
     * @var array
     */
    private static $_di = [];

    /**
     * Set DI
     *
     * @param string $key
     * @param object $object
     *
     * @return object
     */
    final public function setDI($key, $object)
    {
        self::$_di[$key] = $object;
    }

    /**
     * Get DI
     *
     * @param string $key
     *
     * @return object
     */
    final public function getDI($key)
    {
        return (true === isset(self::$_di[$key]))
            ? self::$_di[$key] : null;
    }

    /**
     * Init DI
     *
     * @param string $key
     * @param function $callback
     *
     * @return object
     */
    final public function initDI($key, $callback = null)
    {
        if (false === isset(self::$_di[$key])) {
            self::$_di[$key] = true === is_callable($callback)
                ? $callback() : null;
        }

        return self::$_di[$key];
    }

    /**
     * @var array
     */
    protected $_attr = [];

    /**
     * Set Attr
     *
     * @param string $key
     * @param string $value
     *
     * @return object
     */
    final public function setAttr($key, $value)
    {
        $this->_attr[$key] = $value;

        return $this;
    }

    /**
     * Get Attr
     *
     * @param string $key
     *
     * @return mixed
     */
    final public function getAttr($key)
    {
        return (true === isset($this->_attr[$key]))
            ? $this->_attr[$key] : null;
    }
}
