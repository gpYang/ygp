<?php

/**
 * 模型层基类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

use Library\Db\Db;

/**
 * @package System
 */
abstract class Model {

    /**
     * @var object db类(单例模式)
     */
    protected $db = null;

    /**
     * @var string 写库
     */
    protected $writer = '';

    /**
     * @var string 读库
     */
    protected $reader = '';

    /**
     * 构造函数 实例化db
     * 
     * @param string $writer 主库
     * @param string $reader 从库
     */
    protected function __construct($writer = 'writer', $reader = 'reader') {
        if (!Db::$_dbConfigs) {
            $dbConfig = \System\Config::getConfig('db');
            Db::setDbConfig($dbConfig);
        }
        $this->db = new Db($writer, $reader);
    }

    /**
     * 切换读库
     * 
     * @return object
     */
    protected function reader() {
        return $this->db->setScheme($this->reader);
    }

    /**
     * 切换写库
     * 
     * @return object
     */
    protected function writer() {
        return $this->db->setScheme($this->writer);
    }

    /**
     * 魔术方法 动态调用db类方法
     * @param string $name 方法名
     * @param array $argument 参数
     * @return mixed
     */
    public function __call($name, $argument) {
        if (method_exists($this->db, $name)) {
            return call_user_func_array(array($this->db, $name), $argument);
        }
        thrower('无效数据库方法:' . $name);
    }

}
