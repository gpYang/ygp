<?php

/**
 * 配置操作类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

/**
 * @package System
 */
class Config {

    /**
     * @var array 配置信息
     */
    private static $_configs = array();

    /**
     * 初始化配置
     * 
     * @param array $configs 配置信息
     * @return array
     */
    public static function init($configs) {
        self::$_configs = $configs;
        return $configs;
    }

    /**
     * 获取配置(可用/获取子配置)
     * 
     * @param string $config 配置路径
     * @return array|string
     */
    public static function getConfig($config) {
        $result = null;
        if (empty($config)) {
            return self::$_configs;
        }
        if (strpos($config, '/')) {
            $ex = explode('/', $config);
            $result = self::$_configs;
            foreach ($ex as $value) {
                if (!array_key_exists($value, $result)) {
                    return null;
                }
                $result = $result[$value];
            }
            return $result;
        }
        return array_key_exists($config, self::$_configs) ? self::$_configs[$config] : null;
    }

    /**
     * 设置配置(静态)(可用/设置子配置)
     * 
     * @param string $config 配置路径
     * @param string|array $value 值
     * @return array
     */
    public static function setConfig($config, $value) {
        $explodes = explode('/', trim($config, '/'));
        $result = &self::$_configs;
        foreach ($explodes as $explode) {
            $result = &$result[$explode];
        }
        $result = $value;
        return self::$_configs;
    }

    /**
     * 获取配置目录下文件配置
     * 
     * @param string $name 文件名
     * @return array|null
     */
    public static function getConfigFromFile($name) {
        $file = PATH_APPLICATION . '/Common/Config/' . $name . PHP_EXT;
        if (file_exists($file)) {
            return (include $file);
        }
        return null;
    }

}

?>