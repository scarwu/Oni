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
        $this->res->html('about/default', [
            'title' => 'Oni - About / Default Page'
        ]);
    }

    public function mvcAction()
    {
        $this->res->html('about/mvc', [
            'title' => 'Oni - About / MVC Page'
        ]);
    }

    public function mvvmAction()
    {
        $this->res->html('about/mvvm', [
            'title' => 'Oni - About / MVVM Page'
        ]);
    }
}
