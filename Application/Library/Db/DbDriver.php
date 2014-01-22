<?php

/**
 * 数据库操作基类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Db
 */

namespace Library\Db;

/**
 * @package Library.Db
 */
abstract class DbDriver {

    /**
     * @var string|boolan|int 配置
     */
    protected $port = '';
    protected $hostname = '';
    protected $username = '';
    protected $password = '';
    protected $link = '';
    protected $database = '';
    protected $charset = '';
    protected $table_prefix = '';
    protected $pconnect = false;
    protected $querynum = 0;

    /**
     * 构造函数
     * 
     * @param type $config
     */
    public function __construct($config) {
        $this->port = $config['port'];
        $this->hostname = $config['hostname'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->charset = $config['characterset'];
        $this->pconnect = $config['pconnect'];
        $this->connect();
        $this->setDb($config['database']);
        $this->setPrefix($config['table_prefix']);
    }

    /**
     * 选择库
     * 
     * @param string $db 库名
     */
    public function setDb($db) {
        $this->database = $db;
        $this->select_db($db);
    }

    /**
     * 设置前缀
     * 
     * @param string $tb
     */
    public function setPrefix($tb) {
        $this->table_prefix = $tb;
    }

    /**
     * 获取属性
     * 
     * @param string $name
     */
    public function __get($name) {
        return $this->$name;
    }

}
