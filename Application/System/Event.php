<?php

/**
 * 事件管理器(可在配置文件中任意穿插事件,必须保证路由,引导,完成事件存在配置文件中)
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

use System\View;

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
     * @var object request数据处理器
     */
    protected $request = null;

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
     * 初始化
     */
    public function init() {
        $this->request = new \System\Request(\System\Config::getConfigFromFile('request'));
        $this->router = new \System\Router(config());
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
            } elseif (method_exists($this, $eventName)) {
                call_user_func_array(array($this, $eventName), $event);
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
     * 
     * @param boolean $reMatch 是否重新匹配
     */
    public function match($reMatch = false) {
        $routeMatch = $this->router->getRouteMatch($reMatch);
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
            $layout = $this->matchController->getLayout();
            $common = '';
            if (is_object($matchAction)) {
                $view = $matchAction;
                $matchAction = $view->getData();
            } else {
                $path = implode('/', $this->router->getRouteMatch());
                $view = new View($path, '', $matchAction);
            }
            if ($layout) {
                ob_start();
                $view->includeView();
                $content = ob_get_clean();
                $matchAction['content'] = $content;
                $common = 'layout';
            }
            $this->matchController->view($matchAction, $layout, $common);
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

    /**
     * 获取request数据处理器
     * 
     * @return object
     */
    public function getRequest() {
        return $this->request;
    }

}