<?php

/**
 * mysql操作类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Db
 */

namespace Library\Db\Driver;

use Library\Db\DbDriver;
use Library\Db\DriverInterface;

/**
 * @package Library.Db
 * @subpackage Driver
 */
final class Mysql extends DbDriver implements DriverInterface {

    /**
     * 连接
     * 
     * @param string $dbhost 主机名 
     * @param string $dbuser 用户 
     * @param string $dbpw 密码 
     * @param string $dbname 数据库名 
     * @param int $pconnect 是否持续连接 
     */
    public function connect() {
        if ($this->pconnect) {
            if (!$this->link = @mysql_pconnect($this->hostname, $this->username, $this->password)) {
                $this->throwError("Can not connect to MySQL server");
            }
        } else {
            if (!$this->link = @mysql_connect($this->hostname, $this->username, $this->password)) {
                $this->throwError("Can not connect to MySQL server");
            }
        }
        if ($this->version() > "4.1") {
            if ($this->charset) {
                mysql_query("SET character_set_connection={$this->charset}, character_set_results=$this->charset, character_set_client=binary", $this->link);
            }
            if ($this->version() > "5.0.1") {
                mysql_query("SET sql_mode=''", $this->link);
            }
        }
    }

    /**
     * 选择数据库 
     * 
     * @param string $dbname 数据库名
     * @return boolean
     */
    public function select_db($dbname) {
        if (!$rs = mysql_select_db($dbname, $this->link)) {
            $this->throwError('链接数据库失败,数据库名:' . $dbname);
        }
        return $rs;
    }

    /**
     * 取出结果集中一条记录 
     * 
     * @param resource $resource 连接资源
     * @param int $result_type 返回类型
     * @return array 
     */
    public function fetch_array($resource, $result_type = MYSQL_ASSOC) {  //默认只取关联数组 不取数字数组.
        return mysql_fetch_array($resource, $result_type);
    }

    /**
     * 查询SQL 
     * 
     * @param string $sql sql语句
     * @return object 
     */
    public function query($sql) {
        return mysql_query($sql, $this->link);
    }

    /**
     * 取影响条数 
     * 
     * @return int 
     */
    public function affected_rows() {
        return mysql_affected_rows($this->link);
    }

    /**
     * 返回错误信息 
     * 
     * @return array 
     */
    public function error() {
        return (($this->link) ? mysql_error($this->link) : mysql_error());
    }

    /**
     * 返回错误代码 
     * 
     * @return int 
     */
    public function errno() {
        return intval(($this->link) ? mysql_errno($this->link) : mysql_errno());
    }

    /**
     * 返回查询结果 
     * 
     * @param resource $resource 连接资源
     * @param int $row 行
     * @param mixed $flname 字段
     * @return mixed 
     */
    public function result($resource, $row, $flname = 0) {
        return @mysql_result($resource, $row, $flname);
    }

    /**
     * 结果条数 
     * 
     * @param resource $resource 连接资源
     * @return int 
     */
    public function num_rows($resource) {
        return mysql_num_rows($resource);
    }

    /**
     * 取字段总数 
     * 
     * @param resource $resource 连接资源
     * @return int 
     */
    public function num_fields($resource) {
        return mysql_num_fields($resource);
    }

    /**
     * 释放结果集 
     * 
     * @param resource $resource 连接资源
     * @return boolean
     */
    public function free_result($query) {
        return @mysql_free_result($query);
    }

    /**
     * 返回自增ID 
     * 
     * @return int 
     */
    public function insert_id() {
        return ($id = mysql_insert_id($this->link)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }

    /**
     * 从结果集中取得一行作为枚举数组 
     * 
     * @param resource $resource 连接资源
     * @return array 
     */
    public function fetch_row($resource) {
        return mysql_fetch_row($resource);
    }

    /**
     * 从结果集中取得列信息并作为对象返回 
     * 
     * @param resource $resource 连接资源
     * @return object 
     */
    public function fetch_fields($resource) {
        return mysql_fetch_field($resource);
    }

    /**
     * 返回mysql版本 
     * 
     * @return string 
     */
    public function version() {
        return mysql_get_server_info($this->link);
    }

    /**
     * 关闭连接 
     * 
     * @return boolean
     */
    public function close() {
        return mysql_close($this->link);
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