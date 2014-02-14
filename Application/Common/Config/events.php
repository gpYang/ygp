<?php

/**
 * 事件配置文件(可在框架的任意位置穿插事件)
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Common.Config
 */
return array(
    function() {
        date_default_timezone_set(config('timezone'));
    },
    'route' => array(),
    'match' => array(),
    function($e) {
//        $e->getMatchController()->view(null, 'header', 'header', true);
    },
    'bootstrap' => array(),
    function($e) {
//        $e->getMatchController()->view(null, 'footer', 'footer', true);
        if (config('debug/on')) {
            $e->getMatchController()->view(null, config('debug/debug-path'), config('debug/debug-view'), true);
        }
    },
    'finish' => array(),
);
?>
