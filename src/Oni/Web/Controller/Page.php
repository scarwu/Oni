<?php
declare(strict_types=1);
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
    protected array $_attr = [
        'mode' => 'page'
    ];

    /**
     * @var Req
     */
    protected Req $req;

    /**
     * @var Res
     */
    protected Res $res;

    /**
     * @var View
     */
    protected View $view;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->req = Req::init();
        $this->res = Res::init();
        $this->view = View::init();
    }

    /**
     * Up: Execute before xxxAction
     *
     * @return bool
     */
    public function up(): bool
    {
        return true;
    }

    /**
     * Down: Execute after xxxAction
     *
     * @return bool
     */
    public function down(): bool
    {
        return true;
    }
}
