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
        'host' => '192.168.33.4',
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
    'ext' => array(
        'php' => '.php',
        'html' => '.phtml',
        'log' => '.log',
    ),
    'error_reporting' => E_ALL,
    'app_key' => '123',
    'host_url' => 'http://helloygp.me',
    'scheme' => 'http',
    'language' => 'zh_CN',
//    'path' => array(
//        'log' => PATH_PUBLIC . '/_log',
//        'upload' => PATH_PUBLIC . '/Upload'
//    ),
    'route_rule' => array(
        array(array('manual', 'index', 'index'), 'manual'),
        array(array('index', 'index', 'index'), 'user/[:id]/data/[:name]', array('\d+', '\w+')),
        array(array('index', 'index', 'index'), 'user/[:name]/data/[:id]', array('\w+', '\d+')),
        array(array('index', 'index', 'index'), 'user/[:id]/data/', array('\d+', '\w+', '\d+')),
    ),
    'timezone' => 'PRC',
//    'cache' => 'Memcache',
);