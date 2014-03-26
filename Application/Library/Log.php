<?php

/**
 * 日志操作类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library
 */

namespace Library;

/**
 * @package Library
 */
class Log {

    /**
     * @staticvar string 日志目录
     */
    private static $_logPath = '';

    /**
     * @var string 文件名
     */
    private $_filename = '';

    /**
     * @staticvar string 日志级别
     */

    const CRITIAL = 'critial';
    const ERROR = 'error';
    const WARN = 'warn';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';

    /**
     * 设置日志目录
     * 
     * @param type $path
     */
    public static function setLogPath($path) {
        self::$_logPath = $path;
    }

    /**
     * 构造函数
     */
    public function __construct() {
        $this->_filename = date('Y-m-d', time()) . LOG_EXT;
    }

    /**
     * 得到日志路径
     * 
     * @param string 层级路径
     * @return boolean|string
     */
    private function getDir($logLevel) {
        if (!is_dir(self::$_logPath . '/' . $logLevel)) {
            if (!mkdir(self::$_logPath . '/' . $logLevel, 0755, true)) {
                return false;
            }
        }
        return (self::$_logPath . '/' . $logLevel);
        return false;
    }

    /**
     * 记录日志
     * 
     * @param string $description 描述
     * @param stirng $logLevel 日志级别
     * @param mix $log 日志信息
     * @return boolean
     */
    public function setLog($description, $logLevel, $log) {
        if (($dir = $this->getDir($logLevel))) {
            $logMessage = date('Y-m-d H:i:s') . "\tDescription=> '{$description}';\t";
            if (!empty($log)) {
                $logMessage .= 'Log=> ';
                if (is_array($log)) {
                    foreach ($log as $key => $value) {
                        $logMessage .= is_int($key) ? "'{$value}'; " : "'{$key}':'{$value}'; ";
                    }
                } else {
                    $logMessage .= $log;
                }
            }
            file_put_contents($dir . '/' . $this->_filename, $logMessage . PHP_EOL, FILE_APPEND);
            return true;
        }
        return false;
//        $this->throwError('无法创建日志目录');
    }

    /**
     * 抛出异常方法,便于移植
     * 
     * @param string $errorString 错误信息
     */
    public function throwError($errorString) {
        if (function_exists('thrower')) {
            thrower($errorString);
        } else {
            throw new \Exception($errorString);
        }
    }

}