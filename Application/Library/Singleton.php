<?php

/**
 * 单例类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library
 */

namespace Library;

/**
 * @package Library
 */
class Singleton {

    /**
     * @var array 单例数据存储
     */
    private $_singleton = array();

    /**
     * 添加单例
     * 
     * @param string $name 唯一键名
     * @param object $object 对象
     */
    public function addSingleton($name, $object) {
        return $this->_singleton[$name] = $object;
    }

    /**
     * 获取单例对象
     * 
     * @param string $name 唯一键名
     * @return null|object
     */
    public function getSingleton($name) {
        if (isset($this->_singleton[$name])) {
            return $this->_singleton[$name];
        }
        return null;
    }

    /**
     * 获取所有单例数据
     * 
     * @return array
     */
    public function getAllSingleton() {
        return $this->_singleton;
    }

}

?>
