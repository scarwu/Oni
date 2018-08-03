<?php
/**
 * Index Controller Example
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace WebApp\Controller;

use Oni\Web\Controller;

class IndexController extends Controller
{
    public function getAction()
    {
        $this->res->html('index', [
            'title' => 'Oni - A Lightweight PHP Framework for Web & CLI',
            'method' => $this->req->method(),
            'query' => json_encode($this->req->query()),
            'content' => json_encode($this->req->content()),
            'file' => json_encode($this->req->file())
        ]);
    }
}
