<?php

/**
 * 公共函数
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Common
 */

/**
 * 获取或设置配置
 * @example config('path/log', 'd:/')
 * 
 * @param string|null $config 配置
 * @param string|null $value 设置值
 * @return mixed
 */
function config($config = null, $value = null) {
    if (1 === func_num_args()) {
        return System\Config::getConfig($config);
    } else {
        return System\Config::setConfig($config, $value);
    }
}

/**
 * 抛出异常函数
 * 
 * @param string $string 异常信息
 * @throws \System\Thrower
 */
function thrower($string) {
    global $RETURN;
    if ($RETURN) {
        return $RETURN(false, $string);
    } else {
        @ob_clean();
        throw new \System\Thrower($string);
    }
}

/**
 * 调试
 * 
 * @param mixed $var 调试的值
 * @param boolean $echo 是否输出
 * @param null|string $label 标签
 * @param type $strict 是否断点测试
 * @return null|string 
 */
function dump($var, $echo = true, $label = null, $strict = true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES) . "</pre>";
        }
        else
            $output = $label . " : " . print_r($var, true);
    }else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }
    else
        return $output;
}

/**
 * 缓存函数，约定使用斜杠/做为键分隔符
 *
 * @param string $key 缓存的key
 * @param string $val 缓存的val,如果没有则获取对应key的值
 * @param int expire 过期时间(时间戳或 以秒为单位的整数)
 * @return value
 */
function cache($key, $value = null, $expire = 0) {
    $argsNum = func_num_args();
    static $cache = null;
    if ($cache === null) {
        $cacheType = ($type = config('cache')) ? $type : 'FileCache';
        $obj = '\Library\Cache\\' . $cacheType;
        $cache = new $obj();
    }
    if (0 < $argsNum && !empty($key)) {
        if (1 == $argsNum) {
            $value = $cache->get($key);
        } else {
            if (isset($value)) {
                return $cache->set($key, $value, $expire);
            } else {
                return $cache->delete($key);
            }
        }
    }
    return $value;
}

/**
 * 通过时间戳生成Y-m-d H:i:s日期
 * 
 * @param string|null $time 时间戳
 * @return string
 */
function timestamp($time = null) {
    return date('Y-m-d H:i:s', isset($time) ? $time : time());
}

/**
 * 生成随即字符串
 * 
 * @param int $length 字符串长度
 * @param boolean $numeric 是否纯数字
 * @return string
 */
function get_rand($length = 6, $numeric = false) {
    if ($numeric) {
        $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars [mt_rand(0, $max)];
        }
    }
    return $hash;
}

/**
 * 上传函数
 * @example upload('aaaa', '1:1000', array('.jpg','.png'))
 * @example upload('aaaa', '1000:')
 * 
 * @param string $fileInput 控件名称
 * @param string $fileSize 文件大小限制,以:隔开,单位kb
 * @param array $canUploadType 文件后缀限制,要带.
 * @return string|array 成功返回数组,失败返回描述
 */
function upload($fileInput, $fileSize = '', $canUploadType = array()) {
    $upload = new \Library\Upload($fileInput, $fileSize, $canUploadType);
    return $upload->upload();
}

/**
 * 日志
 * @example logs('上传文件成功', 'warn', array('time' => '2013-2-2'))
 * 
 * @staticvar object $log log类实例
 * @staticvar object $ref refectionClass
 * @param string $description 日志描述
 * @param string $logLevel 日志级别 => critial, error, warn, notice, info, debug
 * @param array $logs 日志数据
 * @return boolean
 */
function logs($description, $logLevel = 'info', $logs = array()) {
    $log = singleton('Library-Log');
    $ref = singleton('ReflectionClass-Log');
    if (null === $log) {
        $log = singleton('Library-Log', new \Library\Log());
    }
    if (null === $ref) {
        $ref = singleton('ReflectionClass-Log', new ReflectionClass($log));
    }
    $logLevels = $ref->getConstants();
    if (!in_array($logLevel, $logLevels)) {
        return false;
    }
    return $log->setLog($description, $logLevel, $logs);
}

/**
 * 获取客户端IP
 * @staticvar mixed $realip
 * @return mixed IP
 */
function get_client_ip() {
    static $realip = null;
    if ($realip !== null) {
        return $realip;
    }
    if (isset($_SERVER)) {
        if (isset($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER ['HTTP_X_FORWARDED_FOR']);
            /* 取X-Forwarded-For中第一个非unknown的有效IP字符串 */
            foreach ($arr as $ip) {
                $ip = trim($ip);
                if ($ip != 'unknown') {
                    $realip = $ip;
                    break;
                }
            }
        } elseif (isset($_SERVER ['HTTP_CLIENT_IP'])) {
            $realip = $_SERVER ['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER ['REMOTE_ADDR'])) {
                $realip = $_SERVER ['REMOTE_ADDR'];
            } else {
                $realip = '0.0.0.0';
            }
        }
    } else {
        if (getenv('HTTP_X_FORWARDED_FOR')) {
            $realip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $realip = getenv('HTTP_CLIENT_IP');
        } else {
            $realip = getenv('REMOTE_ADDR');
        }
    }
    $onlineip = null;
    preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
    $rIp = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
    return $rIp;
}

/**
 * 分页函数
 *
 * @param int $itemCount 总共多少条数据
 * @param int $page 当前页码
 * @param int $pageItem 每页多少条数据
 * @param int $style 样式类型
 * 1=>简易分页,只有上下页和输入页码跳转
 * @return string
 */
function page($itemCount, $page = 1, $pageItem = 20, $pageName = 'p', $style = 1) {
    $pager = new \Library\Pager($itemCount, $pageItem, $style);
    $pager->setUrl(url(\System\Request::getRequestUri(), array($pageName => '_page_')));
    return $pager->show($page);
}

/**
 * 单例函数,根据参数个数判断
 * 无传参获取所有单例
 * 一个参数获取某个单例
 * 其余添加单例
 * 
 * @staticvar object $class 单例类
 * @param string $name 单例键名
 * @param object|null $object 对象
 * @return array|object
 */
function singleton($name = '', $object = null) {
    static $class = null;
    if (null === $class) {
        $class = new \Library\Singleton();
    }
    switch (func_num_args()) {
        case 0 :
            return $class->getAllSingleton();
        case 1 :
            return $class->getSingleton($name);
        default :
            return $class->addSingleton($name, $object);
    }
}