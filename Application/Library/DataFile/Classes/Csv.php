<?php

/**
 * Csv操作类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.DataFile
 */

namespace Library\DataFile\Classes;

use Library\DataFile\DataFileInterface;

/**
 * @package Library.DataFile
 * @subpackage Classes
 */
class Csv implements DataFileInterface {

    /**
     * @var int 行数 
     */
    public $row = 0;

    /**
     * @var int 列数
     */
    public $column = 0;

    /**
     * 导入
     * 
     * @param string $path 导入文件路径
     * @param string $filename 导入文件名
     * @param null|int $sheet 操作的模板块,null为现打开的块
     * @return array
     */
    public function Import($path, $filename, $sheet = null) {
        $row = 1; //第一行开始  
        if (($handle = fopen($path . '/' . $filename, "r")) !== false) {
            while (($dataSrc = fgetcsv($handle)) !== false) {
                $num = count($dataSrc);
                for ($c = 0; $c < $num; $c++) {//列 column   
                    $data[] = $dataSrc[$c]; //字段名称  
                }
                if (!empty($data)) {
                    $dataRtn[] = $data;
                    unset($data);
                }
                $row++;
            }
            fclose($handle);
            $this->setRowAndColumn(array('row' => $row, 'column' => $num));
            return $dataRtn;
        }
    }

    /**
     * 导出/下载
     * 
     * @param array $data
     * @param string $filename
     */
    public function Export($data, $filename) {
        header('Content-type: text/csv');

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

        $csv_row = array();
        foreach ($data as $key => $csv_item) {
            if ($key === 0) {
                $csv_row[] = implode("\t", $csv_item);
                continue;
            }
            $current = array();
            foreach ($csv_item AS $item) {
                $current[] = is_numeric($item) ? $item : '"' . str_replace('"', '""', $item) . '"';
            }
            $csv_row[] = implode("\t", $current);
        }
        $csv_string = implode("\r\n", $csv_row);
        echo "\xFF\xFE" . mb_convert_encoding($csv_string, 'UCS-2LE', 'UTF-8');
    }

    /**
     * 设置行数和列数
     * 
     * @param array $data 函数和列数数据
     */
    private function setRowAndColumn($data) {
        $this->row = $data['row'];
        $this->column = $data['column'];
    }

}