<?php
/**
 * Oni Controller Class
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

use Oni\Web\Req;
use Oni\Web\Res;

abstract class Controller
{
    protected $req = null;
    protected $res = null;

    public function __construct($req = null, $res = null) {
        $this->req = (null !== $req) ? $req : new Req();
        $this->res = (null !== $res) ? $res : new Res();
    }

    public function up()
    {
        // nothing here
    }

    public function down()
    {
        // nothing here
    }
}
