<?php

/**
 * 框架内主导文件
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 */
if (PHP_VERSION < '5.3') {
    throw new Exception('php版本不得低于5.3');
}

include PATH_APPLICATION . '/System/Loader' . PHP_EXT; 

include PATH_APPLICATION . '/Common/function' . PHP_EXT;

$systemObjects['loader'] = new System\Loader((array('System\Event', 'System\Config')));

$config = System\Config::init(System\Config::getConfigFromFile('config'));

error_reporting($config['error_reporting']);

$event = new System\Event($systemObjects);
$event->setEvents(System\Config::getConfigFromFile('events'));
return $event;