<?php
/**
 * Api/Test Controller Example
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace WebApp\Controller\Api;

use Oni\Web\Controller;

class TestController extends Controller
{
    private $option;
    private $data;

    public function up()
    {
        $this->option = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        $this->data = [
            'method' => $this->req->method(),
            'method' => $this->req->method(),
            'query' => $this->req->query(),
            'content' => $this->req->content(),
            'file' => $this->req->file(),
            'native' => [
                'server' => $_SERVER,
                'post' => $_POST,
                'get' => $_GET
            ]
        ];
    }

    public function getAction()
    {
        $this->res->json($this->data, $this->option);
    }

    public function postAction()
    {
        $this->res->json($this->data, $this->option);
    }

    public function putAction()
    {
        $this->res->json($this->data, $this->option);
    }

    public function deleteAction()
    {
        $this->res->json($this->data, $this->option);
    }
}
