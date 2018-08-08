<?php
/**
 * Task
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\CLI;

use Exception;
use Oni\Basic;
use Oni\CLI\In;
use Oni\CLI\Out;

abstract class Task extends Basic
{
    /**
     * @var array
     */
    protected $in = null;

    /**
     * @var array
     */
    protected $out = null;

    /**
     * Construct
     */
    public function __construct($in = null, $out = null) {
        $this->in = (null !== $in) ? $in : In::init();
        $this->out = (null !== $out) ? $out : Out::init();
    }

    /**
     * @var array
     */
    private static $_arguments = [];

    /**
     * @var array
     */
    private static $_options = [];

    /**
     * @var array
     */
    private static $_configs = [];

    /**
     * @var string
     */
    private static $_prefix = null;

    /**
     * @var string
     */
    protected static $_namespace = null;

    /**
     * Execute before run
     */
    public function up() {}

    /**
     * Execute after run
     */
    public function down() {}

    /**
     * Execute run
     */
    abstract public function run();

    /**
     * Get Options
     *
     * @return string $key
     *
     * @return array|bool
     */
    final protected function getArguments($key = null)
    {
        if (null !== $key) {
            if (array_key_exists($key, self::$_arguments)) {
                return self::$_arguments[$key];
            } else {
                return false;
            }
        }

        return self::$_arguments;
    }

    /**
     * Get Options
     *
     * @return string $key
     *
     * @return array|bool
     */
    final protected function getOptions($key = null)
    {
        if (null !== $key) {
            if (array_key_exists($key, self::$_options)) {
                return self::$_options[$key];
            } else {
                return false;
            }
        }

        return self::$_options;
    }

    /**
     * Get Configs
     *
     * @return string $key
     *
     * @return array|bool
     */
    final protected function getConfigs($key = null)
    {
        if (null !== $key) {
            if (array_key_exists($key, self::$_configs)) {
                return self::$_configs[$key];
            } else {
                return false;
            }
        }

        return self::$_configs;
    }

    /**
     * Has Arguments
     *
     * @return bool
     */
    final protected function hasArguments()
    {
        return count(self::$_arguments) > 0;
    }

    /**
     * Has Options
     *
     * @return string $key
     *
     * @return bool
     */
    final protected function hasOptions($key = null)
    {
        if (null !== $key) {
            return array_key_exists($key, self::$_options);
        }

        return count(self::$_options) > 0;
    }

    /**
     * Has Configs
     *
     * @return string $key
     *
     * @return bool
     */
    final protected function hasConfigs($key = null)
    {
        if (null !== $key) {
            return array_key_exists($key, self::$_configs);
        }

        return count(self::$_configs) > 0;
    }
}
