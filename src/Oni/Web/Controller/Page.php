<?php
/**
 * Page
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
use Oni\Web\View;

abstract class Page extends Basic
{
    /**
     * @var array
     */
    protected $_attr = [
        'mode' => 'page'
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
     * @var object
     */
    protected $view = null;

    /**
     * Construct
     */
    public function __construct($req = null, $res = null, $view = null)
    {
        $this->req = (null !== $req) ? $req : Req::init();
        $this->res = (null !== $res) ? $res : Res::init();
        $this->view = (null !== $view) ? $view : View::init();
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
