<?php
/**
 * Response
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

use Exception;
use Oni\Basic;

class Res extends Basic
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
    private function __construct() {
        $this->_attr = [
            'view' => false,
            'view/ext' => 'php'
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
     * Render HTML
     *
     * @param string $_name
     * @param array $_data
     */
    public function html($_name, $_data = [])
    {
        header('Content-Type: text/html');

        $_prefix = $this->_attr['view'];
        $_ext = $this->_attr['view/ext'];
        $_path = "{$_prefix}/{$_name}.{$_ext}";

        if (file_exists($_path)) {
            foreach ($_data as $_key => $_value) {
                $$_key = $_value;
            }

            include $_path;
        }
    }

    /**
     * Render JSON
     *
     * @param array $data
     * @param integer $option
     */
    public function json($data = null, $option = null)
    {
        header('Content-Type: application/json');

        if (null !== $data) {
            echo json_encode($data, $option);
        }
    }
}
