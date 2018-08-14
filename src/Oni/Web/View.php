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

class View extends Basic
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
    private function __construct()
    {
        // Set Default Attributes
        $this->_attr = [
            'path' => null,
            'ext' => 'php'
        ];
    }

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
     * @var string
     */
    private $layoutPath = null;

    /**
     * @var string
     */
    private $contentPath = null;

    /**
     * Set Layout Path
     *
     * @param string $path
     */
    public function setLayoutPath($path)
    {
        $this->layoutPath = $path;
    }

    /**
     * Set Content Path
     *
     * @param string $path
     */
    public function setContentPath($path)
    {
        $this->contentPath = $path;
    }

    /**
     * Get Layout Path
     *
     * @param string $path
     */
    public function getLayoutPath($path)
    {
        return $this->layoutPath;
    }

    /**
     * Get Content Path
     *
     * @param string $path
     */
    public function getContentPath($path)
    {
        return $this->contentPath;
    }

    /**
     * Load Layout
     */
    private function loadLayout()
    {
        $_path = $this->getAttr('path');
        $_ext = $this->getAttr('ext');

        $_fullpath = "{$_path}/{$this->layoutPath}.{$_ext}";

        if (file_exists($_fullpath)) {
            include $_fullpath;
        }
    }

    /**
     * Load Content
     */
    private function loadContent()
    {
        $_path = $this->getAttr('path');
        $_ext = $this->getAttr('ext');

        $_fullpath = "{$_path}/{$this->contentPath}.{$_ext}";

        if (file_exists($_fullpath)) {
            include $_fullpath;
        }
    }

    /**
     * Render
     *
     * @param array $_data
     */
    public function render($_data = [])
    {
        foreach ($_data as $_key => $_value) {
            $$_key = $_value;
        }

        ob_start();
        $this->loadLayout();
        $_result = ob_get_contents();
        ob_end_clean();

        return $_result;
    }
}
