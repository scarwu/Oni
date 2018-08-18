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
        $this->view->setContentPath('about/default');
        $this->view->setData([
            'title' => 'Oni - About / Default Page'
        ]);
    }

    public function mvcAction()
    {
        $this->view->setContentPath('about/mvc');
        $this->view->setData([
            'title' => 'Oni - About / MVC Page'
        ]);
    }

    public function mvvmAction()
    {
        $this->view->setContentPath('about/mvvm');
        $this->view->setData([
            'title' => 'Oni - About / MVVM Page'
        ]);
    }
}
