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
        $this->view->setData([
            'title' => 'Oni - About / Default Page'
        ]);

        $this->res->html($this->view->render());
    }

    public function mvcAction()
    {
        $this->view->setLayoutPath('index');
        $this->view->setContentPath('about/mvc');
        $this->view->setData([
            'title' => 'Oni - About / MVC Page'
        ]);

        $this->res->html($this->view->render());
    }

    public function mvvmAction()
    {
        $this->view->setLayoutPath('index');
        $this->view->setContentPath('about/mvvm');
        $this->view->setData([
            'title' => 'Oni - About / MVVM Page'
        ]);

        $this->res->html($this->view->render());
    }
}
