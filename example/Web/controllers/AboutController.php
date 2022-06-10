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
    /**
     * Actions
     */
    public function defaultAction()
    {
        $this->view->setData([
            'title' => 'Oni - About / Default Page'
        ]);
    }

    public function mvcAction()
    {
        $this->view->setData([
            'title' => 'Oni - About / MVC Page'
        ]);
    }

    public function mvvmAction()
    {
        $this->view->setData([
            'title' => 'Oni - About / MVVM Page'
        ]);
    }
}
