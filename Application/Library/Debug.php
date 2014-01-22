<?php

/**
 * debug测试类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library
 */

namespace Library;

/**
 * @package Library
 */
class Debug {

    /**
     * @var int 记录加载的文件数
     */
    public static $_fileCount = 0;

    /**
     * @var boolean debug开关
     */
    private static $_isOpen = false;

    /**
     * @var array 记录sql语句
     */
    public static $_sql = array();

    /**
     * @var array 记录加载的文件
     */
    public static $_incFile = array();

    /**
     * @var array 记录运行时间
     */
    public static $_time = array();

    /**
     * @var array 记录错误的信息
     */
    public static $_errorInfo = array();

    /**
     * @var array 记录所有运行时间
     */
    public static $_timeLog = array();

    /**
     * @var array 记录所有sql语句
     */
    public static $_sqlLog = array();

    /**
     * 打开开关
     *
     * @param boolean $isOpen 是否开启debug
     * @return boolean
     */
    public static function open($isOpen) {
        self::$_isOpen = $isOpen;
    }

    /**
     * 检查是否打开开关
     *
     * @return boolean
     */
    public static function check() {
        return self::$_isOpen;
    }

    /**
     * 存放查询的sql语句
     *
     * @param string $sql sql语句
     */
    public static function setSql($sql) {
        self::$_sql[] = $sql;
        self::setTime();
    }

    /**
     * 获取sql语句
     *
     * @return array
     */
    public static function getSql() {
        return self::$_sql;
    }

    /**
     * 获取加载的文件,并可正则去掉不需要的文件
     *
     * @param string 匹配正则表达式
     * @param boolean 是否匹配
     */
    public static function getFile($perg = '', $perg_route = true) {
        $incFiles = get_included_files();
        foreach ($incFiles as $incFile) {
            if (!empty($perg)) {
                if ($perg_route == preg_match($perg, $incFile)) {
                    self::$_fileCount++;
                    self::$_incFile[] = $incFile;
                }
            } else {
                self::$_fileCount++;
                self::$_incFile[] = $incFile;
            }
        }
    }

    /**
     * 为一个需要记录时间的内容设置开始时间
     *
     * @param string $label
     */
    public static function setTime($label = '') {
        if (empty($label)) {
            self::$_time[] = microtime(true);
        } else {
            self::$_time[$label] = microtime(true);
        }
    }

    /**
     * 获得该程序的运行时间
     *
     * @param string $label 记录的标签
     */
    public static function getTime($label = '') {
        if (empty($label)) {
            self::$_sqlLog[] = $label . self::timing_format(microtime(true) - array_pop(self::$_time));
        } else {
            self::$_timeLog[] = $label . ' :' . self::timing_format(microtime(true) - self::$_time[$label]);
        }
    }

    /**
     * 把时间转换为毫秒值
     *
     * @param timestamp $span 记录的时间差
     * @return string
     */
    private static function timing_format($span) {
        return number_format($span * 1000, 3);
    }

    /**
     * 把所有记录的时间清空
     */
    public static function clearTime() {
        self::$_time = array();
    }

    /**
     * 输出debug信息
     *
     * @param string $obj 变量名
     * @param string $label 标签名
     * @param string $topLabel 顶级标签名
     */
    public static function show($obj, $label = 'li', $topLabel = 'ul') {
        if ($obj == '_sqlLog') {
            echo array_sum(self::$$obj);
            return;
        }
        echo "<{$topLabel}>";
        if (is_array(self::$$obj)) {
            foreach (self::$$obj as $k => $value) {
                $extend = '';
                if ($obj == '_sql') {
                    $extend = '<strong style = "margin-left:20px;">' . self::$_sqlLog[$k] . 'MS</strong>';
                }
                if ($obj == '_timeLog') {
                    $extend = 'MS';
                }
                if (is_array($value)) {
                    foreach ($value as $v) {
                        echo "<{$label}>{$v}{$extend}</{$label}>";
                    }
                } else {
                    echo "<{$label}>{$value}{$extend}</{$label}>";
                }
            }
        } else {
            echo "<{$label}>{self::$$obj}</{$label}>";
        }
        echo "</{$topLabel}>";
    }

    /**
     * 开启debug测试的运行函数
     * 
     * @param int $no 错误类型
     * @param string $msg 错误信息
     * @param string $file 出错文件
     * @param int $line 出错行数
     * @return boolean
     */
    public static function errorAgent($no, $msg, $file, $line) {
        $msg = "[" . date('Y-m-d H:i:s') . "] {$msg} (in [<strong>{$file}</strong>] on line <strong>{$line}</strong>)";
        switch ($no) {
            case E_NOTICE:
                self::$_errorInfo['E_NOTICE'][] = "[<strong>Notice</strong>]          {$msg}";
                break;
            case E_USER_NOTICE:
                self::$_errorInfo['E_NOTICE'][] = "[<strong>User Notice</strong>]     {$msg}";
                break;
            case E_WARNING:
                self::$_errorInfo['E_WARNING'][] = "[<strong>Warning</strong>]         {$msg}";
                break;
            case E_USER_WARNING:
                self::$_errorInfo['E_WARNING'][] = "[<strong>User Warning</strong>]    {$msg}";
                break;
            case E_CORE_WARNING:
                self::$_errorInfo['E_WARNING'][] = "[<strong>Core Warning</strong>]    {$msg}";
                break;
            case E_COMPILE_WARNING:
                self::$_errorInfo['E_WARNING'][] = "[<strong>Compile Warning</strong>] {$msg}";
                break;
            case E_PARSE:
                self::$_errorInfo['E_PARSE'][] = "[<strong>Parse</strong>]           {$msg}";
                break;
            case E_STRICT:
                self::$_errorInfo['E_STRICT'][] = "[<strong>Strict</strong>]          {$msg}";
                break;
            case E_ERROR:
                self::$_errorInfo['E_ERROR'][] = "[<strong>Error</strong>]           {$msg}";
                break;
            case E_USER_ERROR:
                self::$_errorInfo['E_ERROR'][] = "[<strong>User Error</strong>]      {$msg}";
                break;
            case E_CORE_ERROR:
                self::$_errorInfo['E_ERROR'][] = "[<strong>Core Error</strong>]      {$msg}";
                break;
            case E_COMPILE_ERROR:
                self::$_errorInfo['E_ERROR'][] = "[<strong>Compile Error</strong>]   {$msg}";
                break;
        }
        return true;
    }

}

?>