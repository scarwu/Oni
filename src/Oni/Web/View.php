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

use Oni\Core\Basic;
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
        'paths' => null,
        'ext' => 'php'
    ];

    /**
     * Construct
     *
     * This function is private, so this class is singleton pattern
     */
    private function __construct() {}

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $indexPath = 'index';

    /**
     * @var string
     */
    private $layoutPath = 'layout';

    /**
     * @var string
     */
    private $contentPath = null;

    /**
     * Set Data
     *
     * @param array $data
     *
     * @return bool
     */
    public function setData(array $data): bool
    {
        $this->data = $data;

        return true;
    }

    /**
     * Set Index Path
     *
     * @param string $path
     *
     * @return bool
     */
    public function setIndexPath(string $path): bool
    {
        $this->indexPath = $path;

        return true;
    }

    /**
     * Set Layout Path
     *
     * @param string $path
     *
     * @return bool
     */
    public function setLayoutPath(string $path): bool
    {
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
    public function setContentPath(string $path): bool
    {
        $this->contentPath = $path;

        return true;
    }

    /**
     * Get Index Path
     *
     * @return string|null
     */
    public function getIndexPath(): ?string
    {
        return $this->indexPath;
    }

    /**
     * Get Layout Path
     *
     * @return string|null
     */
    public function getLayoutPath(): ?string
    {
        return $this->layoutPath;
    }

    /**
     * Get Content Path
     *
     * @return string|null
     */
    public function getContentPath(): ?string
    {
        return $this->contentPath;
    }

    /**
     * Load Partial
     *
     * @param string $_targetPath
     *
     * @return string
     */
    private function loadPartial(string $_targetPath): string
    {
        $_result = '';

        if (true === is_string($_targetPath)) {
            $_paths = $this->getAttr('paths');
            $_ext = $this->getAttr('ext');

            $_currentPath = null;

            if (true === in_array(substr($_targetPath, 0, 1), [ '~', '/' ])
                && true === file_exists("{$_targetPath}.{$_ext}")
            ) {
                $_currentPath = "{$_targetPath}.{$_ext}";
            }

            if (false === is_string($_currentPath)) {
                foreach ($_paths as $path) {
                    if (false === file_exists("{$path}/{$_targetPath}.{$_ext}")) {
                        continue;
                    }

                    $_currentPath = "{$path}/{$_targetPath}.{$_ext}";

                    break;
                }
            }

            if (true === is_string($_currentPath)) {
                foreach ($this->data as $_key => $_value) {
                    $$_key = $_value;
                }

                ob_start();
                include $_currentPath;
                $_result = ob_get_contents();
                ob_end_clean();
            }
        }

        return $_result;
    }

    /**
     * Load Index
     *
     * @return string
     */
    private function loadIndex(): string
    {
        return $this->loadPartial($this->indexPath);
    }

    /**
     * Load Layout
     *
     * @return string
     */
    private function loadLayout(): string
    {
        return $this->loadPartial($this->layoutPath);
    }

    /**
     * Load Content
     *
     * @return string
     */
    private function loadContent(): string
    {
        return $this->loadPartial($this->contentPath);
    }

    /**
     * Render
     *
     * @return string
     */
    public function render(): string
    {
        return $this->loadIndex();
    }
}
