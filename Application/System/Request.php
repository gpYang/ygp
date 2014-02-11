<?php

/**
 * http请求类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

/**
 * @package System
 */
class Request {

    /**
     * @var array 处理规则
     */
    private $_role = array();

    /**
     * @var array 处理的数据
     */
    private $_units = array(
        'get', 'post', 'server', 'session', 'cookie'
    );

    /**
     * @var type 请求的uri
     */
    private static $_requestUri = '';

    /**
     * @param array $role 规则
     */
    public function __construct($role) {
        $this->_role = $role;
        $this->setUnits();
        $this->setRequestUri();
    }

    /**
     * 处理数据
     */
    private function setUnits() {
        foreach ($this->_units as $unit) {
            $method = 'set' . ucfirst($unit);
            if (method_exists($this, $method)) {
                $this->$method();
            }
        }
    }

    /**
     * 设置请求uri
     */
    private function setRequestUri() {
        self::$_requestUri = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . (isset($_SERVER['PATH_INFO']) ? ($_SERVER['PATH_INFO'] . '?' . $_SERVER['QUERY_STRING']) : $_SERVER['REQUEST_URI']);
    }

    /**
     * 获得请求url
     * 
     * @return string
     */
    public static function getRequestUri() {
        return self::$_requestUri;
    }

    /**
     * 处理$_SERVER数据
     */
    private function setServer() {
        if (isset($this->_role['server'])) {
            $_SERVER = call_user_func($this->_role['server'], $_SERVER);
        }
    }

    /**
     * 处理$_GET数据
     */
    private function setGet() {
        if (isset($this->_role['get'])) {
            $_GET = call_user_func($this->_role['get'], $_GET);
        }
    }

    /**
     * 处理$_POST数据
     */
    private function setPost() {
        if (isset($this->_role['post'])) {
            $_POST = call_user_func($this->_role['post'], $_POST);
        }
    }

    /**
     * 处理$_SESSION数据
     */
    private function setSession() {
        if (isset($this->_role['session'])) {
            $_SESSION = call_user_func($this->_role['session'], $_SESSION);
        }
    }

    /**
     * 处理$_COOKIE数据
     */
    private function setCookie() {
        if (isset($this->_role['cookie'])) {
            $_COOKIE = call_user_func($this->_role['cookie'], $_COOKIE);
        }
    }

}

?>
