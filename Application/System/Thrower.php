<?php

/**
 * 异常抛出类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

use \Exception;

/**
 * @package System
 */
class Thrower extends Exception {

    /**
     * 魔术方法重写
     */
    public function __toString() {
        $string = $this->getTraceAsString();
        $this->string = array_reverse(explode('#', $string));
        array_pop($this->string);
        preg_match('/\d (.*?)\((\d*?)\):.*/', end($this->string), $matches);
        $this->file = $matches[1];
        $this->line = $matches[2];
        include PATH_TEMPLATES . '/Error/Thrower' . HTML_EXT;
        exit();
    }

}
