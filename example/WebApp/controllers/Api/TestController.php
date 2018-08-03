<?php
/**
 * Api/Test Controller Example
 *
 * @package     Oni
 * @author      ScarWu
 * @copyright   Copyright (c) ScarWu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace WebApp\Controller\Api;

use Oni\Web\Controller;
use Oni\Web\Req;
use Oni\Web\Res;

class TestController extends Controller
{
    private $option;
    private $json;

    public function up()
    {
        $this->option = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        $this->json = [
            'method' => Req::method(),
            'param' => Req::param(),
            'server' => $_SERVER,
            'post' => $_POST,
            'get' => $_GET
        ];
    }

    public function getAction()
    {
        Res::json($this->json, $this->option);
    }

    public function postAction()
    {
        Res::json($this->json, $this->option);
    }

    public function putAction()
    {
        Res::json($this->json, $this->option);
    }

    public function deleteAction()
    {
        Res::json($this->json, $this->option);
    }
}
