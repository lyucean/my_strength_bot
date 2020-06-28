<?php

namespace msb\core;

use ReflectionClass;
use msb;
use msb\command\Error;

class Action
{
    private $route;
    private $method = 'index';

    public function __construct($route)
    {
        $route = parse_url($route)['path'];
        $route = strtolower($route);
        $parts = explode('/', preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route));
        $parts = array_map('ucfirst', $parts);

        // this is route
        if (!empty($parts[1])) {
            $this->route = ucfirst(strtolower($parts[1]));
        }

        // this is method
        if (!empty($parts[2])) {
            $this->method = ucfirst(strtolower($parts[2]));
        }
    }

    public function execute($registry): void
    {
        // Stop any magical methods being called
        if (substr($this->method, 0, 2) == '__') {
            (new Error($registry))->send('Calls to magic methods are not allowed.');
        }

        $file = DIR_COMMAND . $this->route . '.php';
        $class = preg_replace('/[^a-zA-Z0-9]/', '', $this->route);

        // Initialize the class
        if (!is_file($file)) {
            (new Error($registry))->send('Could not call command /' . strtolower($this->route) . '!');
        }

        include_once($file);

        $class = "msb\\command\\$class";
        $command = new $class($registry);

        $reflection = new ReflectionClass($class);

        if (!$reflection->hasMethod($this->method)) {
            (new Error($registry))->send('Could not call command ' . $this->route . '/' . $this->method . '!');
        }

        call_user_func_array(array($command, $this->method), []);
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getMethod()
    {
        return $this->method;
    }
}
