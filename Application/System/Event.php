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
    public static $events = array();

    /**
     * 设置事件
     * 
     * @param array $events 事件配置
     */
    public function setEvents($events) {
        self::$events = $events;
    }

    /**
     * 获取事件
     * 
     * @return array
     */
    public function getEvents() {
        return self::$events;
    }

    /**
     * 启动
     */
    public function run() {
        foreach (self::$events as $eventName => $event) {
            if (is_object($event)) {
                call_user_func($event, $this);
            } else {
                if (method_exists($this, $eventName)) {
                    $this->$eventName();
                }
            }
        }
    }

    /**
     * 得到路由
     */
    public function route() {
        singleton('System-Router')->route();
    }

    /**
     * 匹配路由
     */
    public function match() {
        $routeMatch = singleton('System-Router')->getRoute();
        $matchClass = ucfirst($routeMatch['module']) . '\\' . ucfirst($routeMatch['controller']) . 'Controller';
        if (!class_exists($matchClass)) {
            singleton('System-Router')->notFound();
        }
        singleton('System-Router')->match(singleton('System-Controller', new $matchClass));
    }

    /**
     * 引导
     */
    public function bootstrap() {
        $routeMatch = singleton('System-Router')->getRoute();
        $matchAction = call_user_func(array(singleton('System-Controller'), $routeMatch['action'] . 'Action'));
        if ($matchAction !== false) {
            singleton('System-Controller')->view($matchAction);
        } else {
            singleton('System-Controller')->cleanView();
        }
    }

    /**
     * 完成
     */
    public function finish() {
        singleton('System-Controller')->render();
    }

}