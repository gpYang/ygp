<?php

/**
 * 事件配置文件(可在框架的任意位置穿插事件)
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Common.Config
 */
return array(
    function($e) {
        $e->getRouter()->addRule(array('manual', 'index', 'index'), 'manual/index.html');
        $e->getRouter()->addRule(array('manual', 'start', 'index'), 'manual/start.html');
        $e->getRouter()->addRule(array('manual', 'config', 'index'), 'manual/config.html');
        $e->getRouter()->addRule(array('manual', 'config', 'global'), 'manual/config-global.html');
        $e->getRouter()->addRule(array('manual', 'config', 'events'), 'manual/config-events.html');
        $e->getRouter()->addRule(array('manual', 'config', 'module'), 'manual/config-module.html');
        $e->getRouter()->addRule(array('manual', 'config', 'others'), 'manual/config-others.html');
    },
    'match' => array(true),
    function($e) {
        $e->getMatchController()->layout('layout_manual');
    },
    'bootstrap' => array(),
    'finish' => array(),
);
?>
