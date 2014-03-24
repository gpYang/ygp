<?php

define('PATH_ROOT', dirname(dirname(__FILE__)));

define('PATH_APPLICATION', PATH_ROOT . '/Application');

define('PATH_JOB', PATH_ROOT . '/Job');

define('PATH_VENDOR', PATH_ROOT . '/Vendor');

define('PATH_MODULE', PATH_APPLICATION . '/Module');

define('PATH_MODEL', PATH_JOB . '/Model');

$s = microtime(true);
$RETURN = 'json';
global $RETURN;

include PATH_JOB . '/function.php';

spl_autoload_register('autoload');

$route = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : trim($_SERVER['REQUEST_URI'], '/');

if (!preg_match('/^\/?[a-z]+\/[a-z]+\/[a-z]+\-*[a-z]*\/?(\?+(.*)+)?$/', $route)) {
    return $RETURN(false, '访问路径有误');
}

if (!empty($route)) {
    $explode = explode('/', $route);
    $path = ucfirst($explode[0]);
    $app = ucfirst($explode[1]);
    $method = ucfirst($explode[2]);
    $file = PATH_JOB . '/' . $path . '/' . $app . '.php';
}

if (!file_exists($file)) {
    return $RETURN(false, sprintf('找不到对应文件[%s]', $file));
}

include $file;

if (!class_exists($app)) {
    return $RETURN(false, sprintf('找不到对应类名[%s]', $app));
}

if (!method_exists($app, $method)) {
    return $RETURN(false, sprintf('找不到对应方法名[%s]', $method));
}

$call = call_user_func_array(array($app, $method), $_GET);
if (!is_array($call)) {
    return $RETURN(false, '返回参数有误');
}
echo number_format((microtime(true) - $s) * 1000, 3);
return call_user_func_array($RETURN, $call);