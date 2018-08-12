<?php
/**
 * Main Controller
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace WebApp\Controller;

use Oni\Web\Controller\Page as Controller;

class MainController extends Controller
{
    public function defaultAction()
    {
        $this->res->html('index', [
            'title' => 'Oni - A Lightweight PHP Framework for Web & CLI',
            'method' => $this->req->method(),
            'query' => json_encode($this->req->query()),
            'content' => json_encode($this->req->content()),
            'file' => json_encode($this->req->file())
        ]);
    }

    public function errorAction()
    {
        $this->res->html('error',[
            'title' => 'Oni - Error Page'
        ]);
    }
}
