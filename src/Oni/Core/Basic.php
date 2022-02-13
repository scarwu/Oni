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
    protected $_attr = [];

    /**
     * Set Attr
     *
     * @param string $key
     * @param mixed $value
     *
     * @return bool
     */
    final public function setAttr(string $key, $value): bool
    {
        $this->_attr[$key] = $value;

        return true;
    }

    /**
     * Get Attr
     *
     * @param string $key
     *
     * @return mixed
     */
    final public function getAttr(string $key)
    {
        return (true === isset($this->_attr[$key]))
            ? $this->_attr[$key] : null;
    }
}
