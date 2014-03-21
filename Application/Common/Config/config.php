<?php

/**
 * 总配置文件
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Common.Config
 */
return array(
    'route' => array(
        'default-module' => 'index',
        'default-controller' => 'index',
        'default-action' => 'index',
    ),
    'memcache' => array(
        'host' => 'localhost',
        'port' => '11211',
    ),
    'db' => array(
        'reader' => array(
            'driver' => 'Mysql',
            'database' => 'test',
            'username' => 'root',
            'password' => '',
            'hostname' => 'localhost',
            'port' => '3306',
            'table_prefix' => '',
            'characterset' => 'utf8',
            'pconnect' => false,
        ),
        'writer' => array(
            'driver' => 'Mysql',
            'database' => 'test',
            'username' => 'root',
            'password' => '',
            'hostname' => 'localhost',
            'port' => '3306',
            'table_prefix' => '',
            'characterset' => 'utf8',
            'pconnect' => false,
        ),
    ),
    'debug' => array(
        'on' => true,
        'debug-path' => 'Debug',
        'debug-view' => 'Debug',
    ),
    'helper' => array(
        'StaticFile'
    ),
    'error_reporting' => E_ALL,
    'route_rule' => array(
    ),
//    'timezone' => 'PRC',
//    'cache' => 'Memcache',
);