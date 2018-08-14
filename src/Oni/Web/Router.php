<?php
/**
 *  Router
 *
 * @package     Oni
 * @author      Scar Wu
 * @copyright   Copyright (c) Scar Wu (https://scar.tw)
 * @link        https://github.com/scarwu/Oni
 */

namespace Oni\Web;

use Oni\Basic;

class Router extends Basic
{
    /**
     * @var array
     */
    private $_rule;

    /**
     * @var int
     */
    private $_defaultRoute;

    /**
     * @var string
     */
    private $_matchRoute;

    /**
     * @var bool
     */
    private $_isMatch;

    /**
     * @var array
     */
    private $_regex;

    /**
     * @var string
     */
    private $_path;

    /**
     * @var string
     */
    private $_method;

    /**
     * Constructor
     */
    public function __construct($method, $path, $route = null)
    {
        $this->_rule = [
            'get' => [
                'path' => [],
                'callback' => [],
                'fullRegex' => []
            ],
            'post' => [
                'path' => [],
                'callback' => [],
                'fullRegex' => []
            ],
            'put' => [
                'path' => [],
                'callback' => [],
                'fullRegex' => []
            ],
            'delete' => [
                'path' => [],
                'callback' => [],
                'fullRegex' => []
            ]
        ];

        $this->_matchRoute = null;
        $this->_defaultRoute = null;
        $this->_isMatch = false;

        $this->_regex = [
            ':?' => '(.+)',
            ':string' => '(\w+)',
            ':numeric' => '(\d+)'
        ];

        $this->_method = strtolower($method);
        $this->_path = $path;

        if (null !== $route) {
            $this->addRouteList($route);
        }
    }

    /**
     * Regex Generator
     *
     * @param string
     *
     * @return string
     */
    private function regexGenerator($path)
    {
        $path = str_replace(['/', '.'], ['\/', '\.'], $path);

        foreach ($this->_regex as $search => $replace) {
            $path = str_replace($search, $replace, $path);
        }

        return sprintf('/^%s$/', $path);
    }

    /**
     * Add Route Rule List
     *
     * @param array
     *
     * @return void
     */
    public function addRouteList($route)
    {
        foreach ($route as $rule) {
            $this->addRoute(
                $rule[0],
                $rule[1],
                isset($rule[2]) ? $rule[2] : 'get',
                isset($rule[3]) ? $rule[3] : false
            );
        }
    }

    /**
     * Add Route Rule
     *
     * @param string
     * @param function object
     * @param string
     * @param bool
     *
     * @return void
     */
    public function addRoute($path, $callback, $method = 'get', $fullRegex = false)
    {
        $method = strtolower($method);

        if (!isset($this->_rule[$method])) {
            $method = 'get';
        }

        $this->_rule[$method]['path'][] = $path;
        $this->_rule[$method]['callback'][] = $callback;
        $this->_rule[$method]['fullRegex'][] = $fullRegex;
    }

    /**
     * Match Route
     *
     * @return string
     */
    public function matchRoute()
    {
        return $this->_matchRoute;
    }

    /**
     * Run Router
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->_rule[$this->_method]['path'] as $index => $path) {
            if ('default' === $path) {
                $this->_defaultRoute = $index;
                continue;
            }

            if (false === $this->_rule[$this->_method]['fullRegex'][$index]) {
                $path = $this->regexGenerator($path);
            }

            if (preg_match($path, $this->_path, $match)) {
                $this->_matchRoute = $path;
                $this->_isMatch = true;
                $this->_rule[$this->_method]['callback'][$index](array_slice($match, 1));

                break;
            }
        }

        if (false === $this->_isMatch) {
            if (null !== $this->_defaultRoute) {
                $this->_matchRoute = 'default';
                $this->_rule[$this->_method]['callback'][$this->_defaultRoute]();
            } else {
                header('HTTP\1.1 404 Not Found');
            }
        }
    }
}
