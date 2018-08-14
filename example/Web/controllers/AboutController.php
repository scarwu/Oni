<?php
/**
 * About Controller
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace WebApp\Controller;

use Oni\Web\Controller\Page as Controller;

class AboutController extends Controller
{
    public function defaultAction()
    {
        $this->view->setLayoutPath('index');
        $this->view->setContentPath('about/default');

        $this->res->html($this->view->render([
            'title' => 'Oni - About / Default Page'
        ]));
    }

    public function mvcAction()
    {
        $this->view->setLayoutPath('index');
        $this->view->setContentPath('about/mvc');

        $this->res->html($this->view->render([
            'title' => 'Oni - About / MVC Page'
        ]));
    }

    public function mvvmAction()
    {
        $this->view->setLayoutPath('index');
        $this->view->setContentPath('about/mvvm');

        $this->res->html($this->view->render([
            'title' => 'Oni - About / MVVM Page'
        ]));
    }
}
