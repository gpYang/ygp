<?php

/**
 * 控制器基类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

use System\View;

/**
 * @package System
 */
abstract class Controller {

    /**
     * @var array 视图数据
     */
    protected $viewData = array();

    /**
     * @var array 通过事件管理器加载的试图,用于控制
     */
    protected $eventView = array();

    /**
     * @var boolean 是否清除通过事件管理器加载的试图
     */
    protected $cleanView = false;

    /**
     * @var object 事件管理器
     */
    protected $event = null;

    /**
     * @var array 逻辑数据
     */
    public static $logicData = array();

    /**
     * @var array 模型数据
     */
    public static $modelData = array();

    /**
     * 构造方法,保存事务管理器
     * 
     * @param \System\Event $event
     */
    public function __construct(\System\Event $event) {
        $this->event = $event;
    }

    /**
     * 加载逻辑类(通过path获取其他模块逻辑)
     * 
     * @param string $path 逻辑路径
     * @return object
     */
    protected function logic($path = '') {
        $path = $this->setPath($path);
        return $this->getFileByPath($path, 'logicData', 'Logic');
    }

    /**
     * 加载模型类(通过path获取其他模块模型)
     * 
     * @param string $path 模型路径
     * @return object
     */
    protected function model($path = '') {
        $path = $this->setPath($path);
        return $this->getFileByPath($path, 'modelData', 'Model');
    }

    /**
     * 加载视图文件(通过path获取其他模块模型,common获取公共视图)
     * 
     * @param array $data 视图需要展示数据
     * @param string $path 视图路径
     * @param string $common 公共视图路径
     * @param boolean $isEvent 是否通过事件管理器加载
     */
    public function view($data = null, $path = '', $common = '') {
        if ($path === '') {
            $path = implode('/', $this->event->getRouter()->getRouteMatch());
        }
        $this->viewData[$common . $path] = new View($path, $common, $data);
    }

    /**
     * 渲染
     */
    public function render() {
        foreach ($this->viewData as $value) {
            $value->includeView();
        }
    }

    /**
     * 通过uri设置路径
     * 
     * @param stirng $path uri
     * @return string
     */
    private function setPath($path) {
        $routeMatch = $this->event->getRouter()->getRouteMatch();
        if ($path === '') {
            $path = array($routeMatch['module'], $routeMatch['controller']);
        } else {
            $path = trim($path, '/');
            if (false !== strpos($path, '/')) {
                $path = explode('/', $path);
            } else {
                $path = array($routeMatch['module'], $path);
            }
        }
        return $path;
    }

    /**
     * 通过路径获取对象
     * 
     * @param string $path 路径
     * @param string $dataName 数据名
     * @param string $block 模块
     * @return object
     */
    private function getFileByPath($path, $dataName, $block) {
        $name = implode('/', $path);
        if (!isset(self::${$dataName}[$name])) {
            $realPath = PATH_MODULE . '/' . ucfirst($path[0]) . '/' . $block . '/' . ucfirst($path[1]) . $block . PHP_EXT;
            $realName = ucfirst($path[0]) . '\\' . ucfirst($path[1]) . $block;
            if (!file_exists($realPath)) {
                thrower(sprintf('无法找到对应%s文件'), $block === 'Logic' ? '逻辑' : '模型');
            }
            include $realPath;
            self::${$dataName}[$name] = new $realName($this->event);
        }
        return self::${$dataName}[$name];
    }

    /**
     * 设置静态属性(逻辑,模型)
     * 
     * @param string $name 数据名
     * @param array $value 值
     * @return array
     */
    public static function setStatic($name, $value) {
        self::${$name} = $value;
        return self::${$name};
    }

    /**
     * 获取静态属性(逻辑,模型)
     * 
     * @param string $name 属性名
     * @return array
     */
    public static function getStatic($name) {
        return self::${$name};
    }

    /**
     * 获取事务管理器
     * 
     * @return object
     */
    public function getEvent() {
        return $this->event;
    }

}