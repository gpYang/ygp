<?php

/**
 * 数据文件操作类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.DataFile
 */

namespace Library;

/**
 * @package Library.DataFile
 */
class DataFile {

    /**
     * @var array 导入或导出文件类型
     */
    private static $type = array(
        'csv' => '\Library\DataFile\Csv',
        'xlsx' => '\Library\DataFile\Excel',
        'xls' => '\Library\DataFile\Excel'
    );

    /**
     * @var object 操作的类库
     */
    private static $class = null;

    /**
     * @var int 行数
     */
    private static $row = 0;

    /**
     * @var int 列数
     */
    private static $column = 0;

    /**
     * 初始化
     * 
     * @param string $filename 文件名
     */
    private static function init($filename) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        self::$class = new self::$type[$extension];
    }

    /**
     * 导入
     * 
     * @param string $path 导入文件路径
     * @param string $filename 导入文件名
     * @param null|int $sheet 操作的模板块,null为现打开的块
     * @return array
     */
    public static function Import($path, $filename) {
        self::init($filename);
        if (self::$class !== null) {
            return call_user_method_array(__FUNCTION__, self::$class, func_get_args());
        }
        return false;
    }

    /**
     * 导出/下载
     * 
     * @param array $data
     * @param string $filename
     */
    public static function Export($data, $filename) {
        self::init($filename);
        if (self::$class !== null) {
            call_user_method_array(__FUNCTION__, self::$class, func_get_args());
        }
        return false;
    }

    /**
     * 获取导入行数
     * 
     * @return int
     */
    public static function getRow() {
        return self::$class->row;
    }

    /**
     * 获取导出列数
     * 
     * @return int
     */
    public static function getColumn() {
        return self::$class->column;
    }

}