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
    /**
     * Lifecycle Functions
     */
    public function up()
    {
        $this->view->setLayoutPath('index');
    }

    public function down()
    {
        $this->res->html($this->view->render());
    }

    /**
     * Actions
     */
    public function defaultAction()
    {
        $this->view->setContentPath('main/default');
        $this->view->setData([
            'title' => 'Oni - A Lightweight PHP Framework for Web & CLI',
            'data' => [
                'method' => $this->req->method(),
                'protocol' => $this->req->protocol(),
                'scheme' => $this->req->scheme(),
                'host' => $this->req->host(),
                'uri' => $this->req->uri(),
                'isAjax' => $this->req->isAjax(),
                'contentLength' => $this->req->contentLength(),
                'contentType' => $this->req->contentType(),
                'body' => $this->req->body(),
                'query' => $this->req->query(),
                'content' => $this->req->content(),
                'file' => $this->req->file(),
                'native' => [
                    'server' => $_SERVER,
                    'post' => $_POST,
                    'get' => $_GET
                ]
            ]
        ]);
    }

    public function errorAction()
    {
        $this->view->setContentPath('main/error');
        $this->view->setData([
            'title' => 'Oni - Error Page'
        ]);
    }
}
