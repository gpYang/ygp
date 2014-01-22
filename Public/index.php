<?php

/**
 * 总主导文件
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 */
define('PATH_ROOT', dirname(__DIR__));

define('PATH_PUBLIC', PATH_ROOT . '/Public');

define('PATH_APPLICATION', PATH_ROOT . '/Application');

define('PATH_VENDOR', PATH_ROOT . '/Vendor');

define('PATH_MODULE', PATH_APPLICATION . '/Module');

define('PATH_TEMPLATES', PATH_APPLICATION . '/View');

define('PATH_LIBRARY', PATH_APPLICATION . '/Library');

define('PATH_LOG', PATH_PUBLIC . '/_log');

define('PATH_UPLOAD', PATH_PUBLIC . '/Upload');

define('PATH_CACHE', PATH_PUBLIC . '/_cache');

define('PHP_EXT', '.php');

$handle = include PATH_APPLICATION . '/Handle' . PHP_EXT;

$handle->run();
