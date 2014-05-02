<?php
/**
 * Index Api Example 
 * 
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) 2014, ScarWu (http://scar.simcz.tw/)
 * @link        http://github.com/scarwu/Oni
 */

namespace OniApp\Api;

use Oni;

class IndexApi extends Oni\Api
{
    public function getAction()
    {
        Oni\Res::html('index', [
            'title' => 'Oni - Simple REST Framework',
            'method' => Oni\Req::method(),
            'param' => implode('/', Oni\Req::param())
        ]);
    }
}
