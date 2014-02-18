<?php

/**
 * 逻辑层基类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

use System\Controller;

/**
 * @package System
 */
abstract class Logic {

    /**
     * @var object 事件管理器
     */
    protected $event = null;

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
     * 获取事务管理器
     * 
     * @return object
     */
    protected function getEvent() {
        return $this->event;
    }

    /**
     * 通过url设置路径
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
        $data = Controller::getStatic($dataName);
        $name = implode('/', $path);
        if (!isset($data[$name])) {
            $realPath = PATH_MODULE . '/' . $path[0] . '/' . $block . '/' . $path[1] . $block . PHP_EXT;
            $realName = '\\' . ucfirst($path[0]) . '\\' . ucfirst($path[1]) . $block;
            if (!file_exists($realPath)) {
                thrower('无法找到对应' . ($block === 'Logic' ? '逻辑' : '模型') . '文件');
            }
            include $realPath;
            $data[$name] = new $realName();
            Controller::setStatic($dataName, $data);
        }
        return $data[$name];
    }

}
