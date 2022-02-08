<?php
/**
 * View
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

use Oni\Basic;
use Oni\Web\Helper\HTML;

class View extends Basic
{
    /**
     * @var object
     */
    private static $_instance = null;

    /**
     * Initialize
     */
    public static function init()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * @var array
     */
    protected $_attr = [
        'path' => null,
        'ext' => 'php'
    ];

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * @var string
     */
    private $layoutPath = null;

    /**
     * @var string
     */
    private $contentPath = null;

    /**
     * @var array
     */
    private $data = [];

    /**
     * Set Layout Path
     *
     * @param string $path
     *
     * @return bool
     */
    public function setLayoutPath($path)
    {
        if (false === is_string($path)) {
            return false;
        }

        $this->layoutPath = $path;

        return true;
    }

    /**
     * Set Content Path
     *
     * @param string $path
     *
     * @return bool
     */
    public function setContentPath($path)
    {
        if (false === is_string($path)) {
            return false;
        }

        $this->contentPath = $path;

        return true;
    }

    /**
     * Set Data
     *
     * @param array $data
     *
     * @return bool
     */
    public function setData($data)
    {
        if (false === is_array($data)) {
            return false;
        }

        $this->data = $data;

        return true;
    }

    /**
     * Get Layout Path
     *
     * @return string
     */
    public function getLayoutPath()
    {
        return $this->layoutPath;
    }

    /**
     * Get Content Path
     *
     * @return string
     */
    public function getContentPath()
    {
        return $this->contentPath;
    }

    /**
     * Load Partial
     *
     * @param string $_subPath
     *
     * @return string
     */
    private function loadPartial($_subPath)
    {
        $_result = '';

        if (true === is_string($_subPath)) {
            $_path = $this->getAttr('path');
            $_ext = $this->getAttr('ext');
            $_fullpath = "{$_path}/{$_subPath}.{$_ext}";

            if (true === file_exists($_fullpath)) {
                foreach ($this->data as $_key => $_value) {
                    $$_key = $_value;
                }

                ob_start();
                include $_fullpath;
                $_result = ob_get_contents();
                ob_end_clean();
            }
        }

        return $_result;
    }

    /**
     * Load Content
     *
     * @return string
     */
    private function loadContent()
    {
        return $this->loadPartial($this->contentPath);
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        return $this->loadPartial($this->layoutPath);
    }
}
