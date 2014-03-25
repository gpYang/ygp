<?php

/**
 * Excel操作类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.DataFile
 */

namespace Library\DataFile;

/**
 * @package Library.DataFile
 * @subpackage Classes
 */
class Excel implements DataFileInterface {

    public $row = 0;
    public $column = 0;

    /**
     * 构造函数
     */
    public function __construct() {
        require_once PATH_VENDOR . '/PHPExcel/PHPExcel/IOFactory.php';
    }

    /**
     * 导入
     * 
     * @param string $path 导入文件路径
     * @param string $filename 导入文件名
     * @return array
     */
    public function Import($path, $filename) {
        $excel = \PHPExcel_IOFactory::load($path . '/' . $filename);
        $all = array();
        for ($i = 0; $i < $excel->getSheetCount(); ++$i) {
            $excel->setActiveSheetIndex($i);
            $all[$i] = $excel->getActiveSheet()->toArray();
        }
        return $all;
    }

    /**
     * 导出/下载
     * 
     * @param array $data
     * @param string $filename
     */
    public function Export($data, $filename) {
        header('Content-Type:  application/vnd.ms-excel');

        //处理中文文件名
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($filename);
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }

        $crlf = "\n";
        $t = "\t";
        foreach ($data as $v) {
//            $line = iconv("UTF-8", "GBK", implode($t, $v));
            $line = implode($t, $v);
            echo $line . $crlf;
        }
        exit;
    }

    /**
     * 设置行数和列数
     * 
     * @param object $object phpexcel对象
     */
    private function setRowAndColumn($object) {
        $this->row = $object->getHighestRow();
        $highestColumn = $object->getHighestColumn();
        $this->column = \PHPExcel_Cell::columnIndexFromString($highestColumn);
    }

}