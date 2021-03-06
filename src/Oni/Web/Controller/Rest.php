<?php
/**
 * Rest
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web\Controller;

use Oni\Basic;
use Oni\Web\Req;
use Oni\Web\Res;

abstract class Rest extends Basic
{
    /**
     * @var array
     */
    protected $req = null;

    /**
     * @var array
     */
    protected $res = null;

    /**
     * Construct
     */
    public function __construct($req = null, $res = null)
    {
        // Set Default Attributes
        $this->_attr = [
            'mode' => 'rest'
        ];

        // Set Instances
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
