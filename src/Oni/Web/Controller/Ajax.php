<?php
/**
 * Ajax
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web\Controller;

use Oni\Core\Basic;
use Oni\Web\Http\Req;
use Oni\Web\Http\Res;

abstract class Ajax extends Basic
{
    /**
     * @var array
     */
    protected $_attr = [
        'mode' => 'ajax'
    ];

    /**
     * @var object
     */
    protected $req = null;

    /**
     * @var object
     */
    protected $res = null;

    /**
     * Construct
     */
    public function __construct($req = null, $res = null)
    {
        $this->req = (null !== $req) ? $req : Req::init();
        $this->res = (null !== $res) ? $res : Res::init();
    }

    /**
     * Up Function
     *
     * Execute before xxxAction
     */
    public function up() {}

    /**
     * Down Function
     *
     * Execute after xxxAction
     */
    public function down() {}
}
