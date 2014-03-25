<?php

/**
 * 数据文件类接口
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.DataFile
 */

namespace Library\DataFile;

/**
 * @package Library.DataFile
 */
interface DataFileInterface {

    public function Import($path, $filename);

    public function Export($data, $filename);
}
