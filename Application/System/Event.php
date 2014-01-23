<?php

/**
 * 事件管理器(可在配置文件中任意穿插事件,必须保证路由,引导,完成事件存在配置文件中)
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

/**
 * @package System
 */
class Event {

    /**
     * @var array 事件
     */
    private $_events = array();

    /**
     * @var array 执行过得事件
     */
    private $_ranEvents = array();

    /**
     * @var array 是否有模块事件
     */
    private $_moduleEvent = false;

    /**
     * @var object 路由
     */
    protected $router = null;

    /**
     * @var object 匹配到的控制器
     */
    protected $matchController = null;

    /**
     * @var object 加载器
     */
    protected $loader = null;

    /**
     * 构造方法
     * 
     * @param array $systemObjects 系统类,统一由事务管理器管理
     */
    public function __construct($systemObjects) {
        foreach ($systemObjects as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * 设置事件
     * 
     * @param array $events 事件配置
     */
    public function setEvents($events) {
        $this->_events = $events;
    }

    /**
     * 启动
     */
    public function run() {
        foreach ($this->_events as $eventName => $event) {
            if (true === $this->_moduleEvent) {
                $this->_moduleEvent = false;
                $this->run();
                break;
            }
            if (is_object($event)) {
                call_user_func($event, $this);
            } else {
                if (method_exists($this, $eventName)) {
                    $this->$eventName();
                }
            }
            is_int($eventName) ? $this->_ranEvents[] = $event : $this->_ranEvents[$eventName] = $event;
        }
    }

    /**
     * 得到路由
     */
    public function route() {
        $routeMatch = $this->router->route();
        $eventModuleFile = PATH_MODULE . '/' . ucfirst($routeMatch['module']) . '/events' . PHP_EXT;
        if (file_exists($eventModuleFile)) {
            $this->_events = include $eventModuleFile;
            unset($this->_events['route']);
            $this->_moduleEvent = true;
        }
    }

    /**
     * 匹配路由
     */
    public function match() {
        $routeMatch = $this->router->getRouteMatch();
        $matchClass = ucfirst($routeMatch['module']) . '\\' . ucfirst($routeMatch['controller']) . 'Controller';
        if (!class_exists($matchClass)) {
            $this->router->notFound();
        }
        $this->router->match($this->matchController = new $matchClass($this));
    }

    /**
     * 引导
     */
    public function bootstrap() {
        $routeMatch = $this->router->getRouteMatch();
        $matchAction = call_user_func(array($this->matchController, $routeMatch['action'] . 'Action'));
        if ($matchAction !== false) {
            $this->matchController->view($matchAction);
        }
    }

    /**
     * 完成
     */
    public function finish() {
        $this->matchController->render();
    }

    /**
     * 获取匹配控制器
     * 
     * @return object
     */
    public function getMatchController() {
        return $this->matchController;
    }

    /**
     * 获取路由
     * 
     * @return object
     */
    public function getRouter() {
        return $this->router;
    }

    /**
     * 获取事件
     * 
     * @return array
     */
    public function getEvents() {
        return $this->_ranEvents;
    }

    /**
     * 获取加载器
     * 
     * @return object
     */
    public function getLoader() {
        return $this->loader;
    }

}