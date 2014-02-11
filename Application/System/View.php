<?php

/**
 * 视图层基类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

/**
 * @package System
 */
class View {

    /**
     * @var string 视图根路径
     */
    private $_rootPath = '';

    /**
     * @var string 真实路径
     */
    private $_realPath = '';

    /**
     * @var boolean 是否公共视图
     */
    private $_common = false;

    /**
     * @var array 视图展示数据
     */
    private $_data = array();

    /**
     * @var array 助手类
     */
    private static $_helper = array();

    /**
     * 构造
     * 
     * @param string $path 路径
     * @param string $common 公共模块
     * @param array $data 数据
     */
    public function __construct($path, $common, $data) {
        if (empty(static::$_helper)) {
            static::$_helper = config('helper');
        }
        $this->setRootPath($common);
        $this->setRealPath($path);
        $this->getView($data);
    }

    /**
     * 设置根路径
     * 
     * @param string $common 公共模块名
     */
    public function setRootPath($common) {
        if (empty($common)) {
            $this->_rootPath = PATH_MODULE;
            $this->_common = false;
        } else {
            $this->_rootPath = PATH_TEMPLATES . '/' . ucfirst($common);
            $this->_common = true;
        }
    }

    /**
     * 设置真实路径
     * 
     * @param string $path 路径
     */
    public function setRealPath($path) {
        if ($this->_common === false) {
            $path = explode('/', $path);
            $this->_realPath = $this->_rootPath . '/' . ucfirst($path[0]) . '/View/' . ucfirst($path[1]) . '/' . ucfirst($path[2]) . HTML_EXT;
        } else {
            $this->_realPath = $this->_rootPath . '/' . ucfirst($path) . HTML_EXT;
        }
    }

    /**
     * 得到视图文件
     * 
     * @param array $data 数据
     */
    public function getView($data) {
        if (file_exists($this->_realPath)) {
            if (isset($data) && is_array($data)) {
                $this->_data = $data;
                foreach ($data as $key => $value) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     *  魔术方法 加载助手
     * 
     * @param string $name 方法名
     * @param array $argument 参数
     * @return mixed
     */
    public function __call($name, $argument) {
        $name = ucfirst($name);
        if (in_array($name, static::$_helper)) {
            $helperClass = '\Helper\\' . $name;
            if (class_exists($helperClass)) {
                $helper = new $helperClass;
                return call_user_func_array($helper, $argument);
            }
        }
    }

    /**
     * 在视图中加载视图
     * 
     * @param string $path 路径
     * @param string $common 公共模块
     * @param array $data 数据
     */
    public function view($data = null, $path = '', $common = '') {
        $view = new self($path, $common, $data);
        return $view->includeView();
    }

    /**
     * 加载视图文件
     */
    public function includeView() {
        if (!empty($this->_data)) {
            extract($this->_data);
        }
        include $this->_realPath;
    }

}