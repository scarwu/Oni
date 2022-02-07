<?php
/**
 * Api/Rest Controller
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace WebApp\Controller\Api;

use Oni\Web\Controller\Rest as Controller;

class RestController extends Controller
{
    private $data;

    /**
     * Lifecycle Functions
     */
    public function up()
    {
        $this->data = [
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
        ];
    }

    public function down()
    {
        // do nothing
    }

    /**
     * Actions
     */
    public function getAction()
    {
        $this->res->json($this->data);
    }

    public function postAction()
    {
        $this->res->json($this->data);
    }

    public function putAction()
    {
        $this->res->json($this->data);
    }

    public function deleteAction()
    {
        $this->res->json($this->data);
    }
}
