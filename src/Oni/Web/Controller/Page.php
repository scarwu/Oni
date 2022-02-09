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
     * Initializer
     */
    final public function init()
    {
        $this->req = $this->initDI('req', function () {
            return Req::init();
        });
        $this->res = $this->initDI('res', function () {
            return Res::init();
        });
        $this->view = $this->initDI('view', function () {
            return View::init();
        });
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
