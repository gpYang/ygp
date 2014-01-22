<?php

/**
 * 静态文件助手类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Helper
 */

namespace Helper;

/**
 * @package Helper
 */
class StaticFile {

    /**
     * @var string host地址
     */
    public static $host = '';

    /**
     * 获取静态文件
     * 
     * @param string $dir 目录
     * @param string $name 文件名
     * @return string
     */
    public function __invoke($dir, $name) {
        if (empty(static::$host)) {
            static::$host = config('host_url');
        }
        return static::$host . '/' . ucfirst($dir) . '/' . $name;
    }

}