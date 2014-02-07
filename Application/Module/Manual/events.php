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
    function($e) {
        $e->getRouter()->addRule(array('manual', 'start', 'index'), 'manual/start.html');
        $e->getRouter()->addRule(array('manual', 'start', 'environment'), 'manual/environment.html');
        $e->getRouter()->addRule(array('manual', 'start', 'download'), 'manual/download.html');
    },
    'route' => array(),
    'match' => array(true),
    function($e) {
        $e->getMatchController()->layout('layout_manual');
    },
    'bootstrap' => array(),
    'finish' => array(),
);
?>
