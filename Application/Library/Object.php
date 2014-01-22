<?php

/**
 * 空对象类(暂时只用于任意位置加载视图文件)
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library
 */

namespace Library;

/**
 * @package Library
 */
class Object {

    public function __construct() {
        ;
    }

    public function __destruct() {
        
    }

    public function includeFile($file, $data = null) {
        if (isset($data) && is_array($data)) {
            extract($data);
        }
        return include $file;
    }

    public function __call($name, $argument) {
        
    }

    public function __set($name, $value) {
        $this->$name = $value;
    }

    public function __get($name) {
        return $this->$name;
    }

}