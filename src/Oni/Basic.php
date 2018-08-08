<?php
/**
 * Basic
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni;

abstract class Basic
{
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
     * @return string|null
     */
    final public function getAttr($key)
    {
        return isset($this->_attr[$key])
            ? $this->_attr[$key] : null;
    }
}
