<?php

/**
 * 加载文件类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

/**
 * @package System
 */
class Loader {

    /**
     * @var array 已加载文件
     */
    private $_isLoad = array();

    /**
     * @var array 系统文件路径
     */
    private $_system = array(
        'System', 'Library', 'Common', 'Helper'
    );

    /**
     * @var array 模块文件路径
     */
    private $_module = array(
        'Controller', 'Logic', 'Model'
    );

    /**
     * 构造函数
     * 
     * @param array $loader 加载文件
     */
    public function __construct($loader) {
        $this->factory($loader);
    }

    /**
     * 初始化
     * 
     * @param array $loader 加载文件
     * @return null
     */
    public function factory($loader) {
        if (empty($loader)) {
            return;
        }
        spl_autoload_register(array($this, 'autoload'));
        foreach ($loader as $class) {
            if (!$this->autoload($class)) {
                thrower('缺少系统类' . $class);
            }
        }
    }

    /**
     * 自动加载
     * 
     * @param string $class 类名
     * @return boolean
     */
    public function autoload($class) {
        if (!isset($this->_isLoad[$class])) {
            if (($file = $this->parseClass($class))) {
                if (file_exists($file . PHP_EXT)) {
                    include $file . PHP_EXT;
                    if (class_exists($class) || interface_exists($class)) {
                        $this->_isLoad[$class] = true;
                        return true;
                    }
                }
            }
        } else {
            return true;
        }
        return false;
    }

    /**
     * 解析类名
     * 
     * @param string $class 类名
     * @return boolean
     */
    public function parseClass($class) {
        $class = ltrim(str_replace('\\', '/', $class), '\\');
        if (false !== strpos($class, '/')) {
            $explode = explode('/', $class);
            if (in_array($explode[0], $this->_system)) {
                return PATH_APPLICATION . '/' . $class;
            } else {
                $module = $this->_module;
                $moduleType = preg_replace_callback('/[A-Z][^A-Z]*/', function ($m) use($module) {
                            $map = current($m);
                            if (in_array($map, $module)) {
                                return $map;
                            }
                        }, $explode[1]);
                $moduleName = $explode[0];
                return PATH_MODULE . '/' . $moduleName . '/' . $moduleType . '/' . $explode[1];
            }
        }
        return false;
    }

    /**
     * 获取已加载文件
     * 
     * @return array
     */
    public function getIsLoad() {
        return $this->_isLoad;
    }

}
