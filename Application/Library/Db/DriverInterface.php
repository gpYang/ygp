<?php

/**
 * 数据库操作类接口
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Db
 */

namespace Library\Db;

/**
 * @package Library.Db
 */
interface DriverInterface {

    public function connect();

    public function select_db($dbname);

    public function fetch_array($resource, $result_type);

    public function query($sql);

    public function affected_rows();

    public function error();

    public function errno();

    public function result($resource, $row, $flname);

    public function num_rows($resource);

    public function num_fields($resource);

    public function free_result($resource);

    public function insert_id();

    public function fetch_row($resource);

    public function fetch_fields($resource);

    public function version();

    public function close();
}