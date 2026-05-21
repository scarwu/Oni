<?php
declare(strict_types=1);
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
    protected array $_attr = [
        'mode' => 'ajax'
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
     * Construct
     */
    public function __construct()
    {
        $this->req = Req::init();
        $this->res = Res::init();
    }

    /**
     * Up Function
     *
     * Execute before xxxAction
     */
    public function up(): mixed
    {
        return true;
    }

    /**
     * Down Function
     *
     * Execute after xxxAction
     */
    public function down(): void {}
}
