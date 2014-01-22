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

$systemObjects = array();
$systemObjects['loader'] = new System\Loader(array('System\Router', 'System\Request', 'System\Event', 'System\Config'));

$config = System\Config::init(include PATH_APPLICATION . '/Common/Config/config' . PHP_EXT);

error_reporting($config['error_reporting']);

if ($config['debug']['on']) {
    set_error_handler(array('Library\Debug', 'errorAgent'));
    Library\Debug::open($config['debug']['on']);
    Library\Debug::setTime('allRun');
}

define('HTML_EXT', isset($config['ext']['html']) ? $config['ext']['html'] : '.phtml');
define('LOG_EXT', isset($config['ext']['log']) ? $config['ext']['log'] : '.log');

$systemObjects['request'] = new System\Request(System\Config::getConfigFromFile('request'));
$systemObjects['router'] = new System\Router($config);

$event = new System\Event($systemObjects);
$event->setEvents(System\Config::getConfigFromFile('events'));
return $event;