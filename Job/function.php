<?php

function json($error = false, $message = '系统错误', $data = array()) {
    echo json_encode(array('success' => $error, 'message' => $message, 'data' => $data));
    exit();
}

function model($path, $isJob) {
    global $RETURN;
    global $MODEL;
    global $CONFIG;
    if (empty($path) || (false === $isJob && false === strpos($path, '/'))) {
        return $RETURN(false, '无法找到对应模型文件');
    }
    if (empty($CONFIG)) {
        $CONFIG = System\Config::init(include PATH_APPLICATION . '/Common/Config/config.php');
    }
    if (false === $isJob) {
        $path = explode('/', $path);
        $realPath = PATH_MODULE . '/' . $path[0] . '/Model/' . $path[1] . 'Model.php';
        $realName = ucfirst($path[0]) . '\\' . ucfirst($path[1]) . 'Model';
    } else {
        $realPath = PATH_MODEL . '/' . $path . 'Model.php';
        $path = end(explode('/', $path));
        $realName = ucfirst($path) . 'Model';
    }
    if (!file_exists($realPath)) {
        return $RETURN(false, '无法找到对应模型文件');
    }
    if (!isset($MODEL[$realPath])) {
        include $realPath;
        $MODEL[$realPath] = new $realName;
    }

    return $MODEL[$realPath];
}

function autoload($class) {
    global $ISLOAD;
    global $RETURN;
    if (!isset($ISLOAD[$class])) {
        if (file_exists(PATH_APPLICATION . '/' . $class . '.php')) {
            include PATH_APPLICATION . '/' . $class . '.php';
            if (class_exists($class) || interface_exists($class)) {
                $ISLOAD[$class] = true;
                return true;
            }
        }
    } else {
        return true;
    }
    return $RETURN(false, '无法找到对应类名->' . $class);
}

function thrower($string) {
    global $RETURN;
    return $RETURN(false, $string);
}
