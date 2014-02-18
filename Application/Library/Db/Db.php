<?php

/**
 * db操作类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Db
 */

namespace Library\Db;

use Library\Debug;

/**
 * @package Library.Db
 */
class Db {

    /**
     * @var object sql操作对象
     */
    private $_adapter = null;
    private static $_scheme = array();

    /**
     * @var string|array sql命令
     */
    private $_query = '';
    private $_columns = array();
    private $_from = '';
    private $_join = array();
    private $_where = array();
    private $_group = array();
    private $_having = array();
    private $_order = array();
    private $_limit = '';
    private $_offset = '';

    /**
     * @var string 上次执行命令
     */
    private $_lastSql = '';

    /**
     * @var string 上次插入id
     */
    private $_insertId = '';

    /**
     * @var type 默认操作对象名
     */
    private $_defaultScheme = '';

    /**
     * @var type 当前操作对象名
     */
    private $_currentScheme = '';

    /**
     * @var array 数据库配置
     */
    private static $_dbConfigs = array();

    /**
     * @var boolean 是否自动切换主从库
     */
    private $_autoScheme = true;

    /**
     * @var array 错误信息
     */
    private $_error = array();

    /**
     * @var string 表前缀占位符
     */
    private $_pre = '%pre%';

    /**
     * @var boolean 是否重置
     */
    private $_reset = true;

    /**
     * @var type 主表与关联表
     */
    private $_tables = array();

    /**
     * @var string 从库名
     */
    private $_reader = '';

    /**
     * @var string 主库名
     */
    private $_writer = '';

    /**
     * 构造 设置是否自动切换主从库
     * 
     * @param string $writer 主库名
     * @param string $reader 从库名
     */
    public function __construct($writer, $reader) {
        if (empty(static::$_dbConfigs)) {
            static::$_dbConfigs = config('db');
        }
        $this->_defaultScheme = $this->_writer = $writer;
        $this->_reader = $reader;
        if ($this->_reader == $this->_writer) {
            $this->setAutoScheme(false);
        }
    }

    /**
     * 切换库
     * 
     * @param string $key 库名
     * @return \Library\Db\Db
     */
    public function setScheme($key) {
        if (!isset(static::$_scheme[$key])) {
            if (!isset(static::$_dbConfigs[$key])) {
                $this->throwError(sprintf('无法找到%s对应的数据库配置', $key));
            }
            $driver = '\Library\Db\Driver\\' . static::$_dbConfigs[$key]['driver'];
            static::$_scheme[$key] = new $driver(static::$_dbConfigs[$key]);
        }
        $this->_currentScheme = $key;
        $this->_adapter = static::$_scheme[$key];
        return $this;
    }

    /**
     * 获取sql命中中涉及的表
     * 
     * @return type
     */
    public function getTables() {
        return $this->_tables;
    }

    /**
     * 设置是否自动切换主从库
     * 
     * @param boolean $isAuto 是否自动切换
     */
    public function setAutoScheme($isAuto) {
        $this->_autoScheme = $isAuto;
    }

    /**
     * 主表
     * 
     * @param array|string $table 表名
     * @return \Library\Db\Db
     */
    public function from($table) {
        $this->_tables[is_array($table) ? key($table) : $table] = is_array($table) ? current($table) : $table;
        $this->_from = $table;
        return $this;
    }

    /**
     * 列
     * 
     * @param array $columns 列
     * @return \Library\Db\Db
     */
    public function columns($columns) {
        $this->_columns = $columns;
        return $this;
    }

    /**
     * 关联
     * 
     * @param array|string $join 关联表(string时忽略后面参数)
     * @param string $condition 关联条件
     * @param array|string $columns 获取关联表的列
     * @param string $type 关联类型
     * @return \Library\Db\Db
     */
    public function join($join, $condition, $columns = '*', $type = 'inner') {
        $this->_tables[is_array($join) ? key($join) : $join] = is_array($join) ? current($join) : $join;
        $this->_join[] = array('join' => $join, 'condition' => $condition, 'columns' => $columns, 'type' => strtoupper($type) . ' JOIN');
        return $this;
    }

    /**
     * 条件
     * 
     * @param array|string $where 条件
     * @param string $link 与上个条件的关系
     * @param boolean $predicate 是否加上括号
     * @return \Library\Db\Db
     */
    public function where($where, $link = 'AND', $predicate = false) {
        $this->_where[] = array('where' => $where, 'link' => strtoupper($link), 'predicate' => $predicate);
        return $this;
    }

    /**
     * 分组
     * 
     * @param array|string $group 分组
     * @return \Library\Db\Db
     */
    public function group($group) {
        $this->_group[] = $group;
        return $this;
    }

    /**
     * 分组条件
     * 
     * @param array|string $having 条件
     * @param string $link 与上个条件的关系
     * @param boolean $predicate 是否加上括号
     * @return \Library\Db\Db
     */
    public function having($having, $link = 'AND', $predicate = false) {
        $this->_having[] = array('where' => $having, 'link' => strtoupper($link), 'predicate' => $predicate);
        return $this;
    }

    /**
     * 排序
     * 
     * @param string|array $order 排序
     * @return \Library\Db\Db
     */
    public function order($order) {
        $this->_order[] = $order;
        return $this;
    }

    /**
     * 获取条数
     * 
     * @param string $limit 获取条数
     * @return \Library\Db\Db
     */
    public function limit($limit) {
        $this->_limit = $limit;
        return $this;
    }

    /**
     * 获取起点
     * 
     * @param string $offset 获取起点
     * @return \Library\Db\Db
     */
    public function offset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    /**
     * 获取单条数据(一维)
     * 
     * @return array|false
     */
    public function find() {
        $this->limit(1);
        $this->offset(0);
        return current($this->select());
    }

    /**
     * 获取总数
     * 
     * @return int|false
     */
    public function count($field = null) {
        $this->autoScheme($this->_reader);
        $queryFrom = $this->parseFrom();
        $queryJoin = $this->parseJoin();
        $queryWhere = $this->parseWhere();
        $queryGroup = $this->parseGroup();
        $queryHaving = $this->parseWhere('_having');
        $table = is_array($this->_from) ? current($this->_from) : $this->_from;
        if (!isset($field)) {
            if (!($field = $this->findKey($table))) {
                $field = '*';
            }
        }
        if (!empty($queryJoin)) {
            $field = ('`' . (is_array($this->_from) ? key($this->_from) : $this->_from) . '`.') . $field;
        }
        $column = sprintf('COUNT(%s)', $field);
        $sql = 'SELECT ' . $column . ' FROM ' . $queryFrom . $queryJoin . $queryWhere . $queryGroup . $queryHaving;
        $rs = $this->query($sql);

        $re = false;
        if (!$this->hasError()) {
            if ($this->_adapter->num_rows($rs) >= 1) {
                $re = intval(current($this->_adapter->fetch_row($rs)));
            }
            $this->_adapter->free_result($rs);
        }
        return $re;
    }

    /**
     * 获取分页数据(返回list数组和page数组)
     * 
     * @param int $currentPage 当前页码
     * @param int $pageItem 单页条数
     * @return boolean|array
     */
    public function page($currentPage, $pageItem = 20) {
        $this->_reset = false;
        if (false !== ($count = $this->count())) {
            $this->_reset = true;
            $this->limit($pageItem)->offset($pageItem * ($currentPage - 1));
            return array(
                'page' => array(
                    'count' => $count,
                    'currentPage' => $currentPage,
                    'perPageCount' => $pageItem,
                ),
                'list' => $this->select());
        }
        $this->reset();
        return false;
    }

    /**
     * 获取表键
     * 
     * @param string $table 表名
     * @param string $key 键名
     * @return string|false
     */
    public function findKey($table, $key = 'PRI') {
        $rs = $this->query("SELECT COLUMN_NAME
            FROM
                INFORMATION_SCHEMA. COLUMNS
            WHERE
                table_name = '{$this->_pre}{$table}'
                AND COLUMN_KEY = '{$key}'
            LIMIT 1", false, false);
        $re = false;
        if (!$this->hasError()) {
            if ($this->_adapter->num_rows($rs) >= 1) {
                $re = current($this->_adapter->fetch_row($rs));
            }
            $this->_adapter->free_result($rs);
        }
        return $re;
    }

    /**
     * 查询
     * 
     * @return array|false
     */
    public function select() {
        $this->autoScheme($this->_reader);
        $queryColumns = $this->parseColumns();
        $queryFrom = $this->parseFrom();
        $queryJoin = $this->parseJoin($queryColumns);
        $queryWhere = $this->parseWhere();
        $queryOrder = $this->parseOrder();
        $queryLimit = $this->parseLimit();
        $queryOffset = $this->parseOffset();
        $queryGroup = $this->parseGroup();
        $queryHaving = $this->parseWhere('_having');
//        $sql = sprintf('SELECT %s FROM %s', $queryColumns, $queryFrom . $queryJoin . $queryWhere . $queryLimit . $queryOffset);
        $sql = 'SELECT ' . $queryColumns . ' FROM ' . $queryFrom . $queryJoin . $queryWhere . $queryGroup . $queryHaving . $queryOrder . $queryLimit . $queryOffset;

        $rs = $this->query($sql);
        $re = $this->toArray($rs);
        return $re;
    }

    /**
     * 新增
     * 
     * @param array $data 数据
     * @param string $select 查询插入
     * @return boolean
     */
    public function insert($data, $select = '') {
        $this->autoScheme($this->_writer);
        if (!is_array(current($data))) {
            $data = array(0 => $data);
        }
        $fields = $select === '' ? array_keys(current($data)) : array_values($data);
        $queryField = sprintf(' (`%s`)', implode('`,`', $fields));
        if ($select === '') {
            foreach ($data as $insert) {
                if (array_diff($fields, array_keys($insert)) || array_diff(array_keys($insert), $fields)) {
                    $this->throwError('插入键值必须一一对应');
                }
                $set[] = sprintf("('%s')", implode("','", $insert));
            }
            $querySet = ' VALUES ' . implode(', ', $set);
        } else {
            $querySet = $select;
        }
        $queryFrom = $this->parseFrom();
        $sql = 'INSERT INTO ' . $queryFrom . $queryField . $querySet;
        $this->query($sql);
        $return = false;
        if (!$this->hasError()) {
            $this->_insertId = $this->_adapter->insert_id();
            $return = true;
        }
        return $return;
    }

    /**
     * 删除
     * 
     * @return boolean
     */
    public function delete() {
        $this->autoScheme($this->_writer);
        $queryJoin = $this->parseJoin();
        $queryFrom = $this->parseFrom();
        $queryWhere = $this->parseWhere();
        $sql = 'DELETE FROM ' . $queryFrom . $queryJoin . $queryWhere;
        $this->query($sql);
        $return = false;
        if (!$this->hasError()) {
            $return = true;
        }
        return $return;
    }

    /**
     * 更新
     * 
     * @param array $data 数据
     * @return boolean
     */
    public function update($data) {
        $this->autoScheme($this->_writer);
        $queryJoin = $this->parseJoin();
        $queryFrom = $this->parseFrom();
        $queryWhere = $this->parseWhere();
        $set = ' SET ';
        if ($queryJoin === '') {
            foreach ($data as $key => $value) {
                $set .= sprintf("`%s` = '%s'", $key, $value);
            }
        } else {
            foreach ($data as $key => $value) {
                $set .= sprintf("`%s`.`%s`='%s'", $this->_table, $key, $value);
            }
        }
        $querySet = substr($set, 0, -1);
        $sql = 'UPDATE ' . $queryFrom . $queryJoin . $querySet . $queryWhere;
        $this->query($sql);
        $return = false;
        if (!$this->hasError()) {
            $return = true;
        }
        return $return;
    }

    /**
     * 重置
     */
    public function reset() {
        $this->_query = '';
        $this->_from = '';
        $this->_join = array();
        $this->_where = array();
        $this->_columns = array();
        $this->_limit = '';
        $this->_offset = '';
        $this->_table = '';
        $this->_reset = true;
        if ($this->_currentScheme !== $this->_defaultScheme) {
            $this->_currentScheme = $this->_defaultScheme;
        }
    }

    /**
     * 转化为数组
     * 
     * @param object $rs 结果集
     * @return array
     */
    public function toArray($rs) {
        $re = array();
        if (!$this->hasError()) {
            if ($this->_adapter->num_rows($rs) >= 1) {
                while ($info = $this->_adapter->fetch_array($rs)) {
                    $re[] = $info;
                }
            }
            $this->_adapter->free_result($rs);
        }
        return $re;
    }

    /**
     * 获取是否有错误
     * 
     * @return boolean
     */
    public function hasError() {
        return $this->_error['error'];
    }

    /**
     * 获取错误信息
     * 
     * @return array
     */
    public function getError() {
        return array(
            'code' => $this->_error['code'],
            'message' => $this->_error['message']
        );
    }

    /**
     * 获取最后一条sql语句
     * 
     * @return string
     */
    public function getLastSql() {
        return $this->_lastSql;
    }

    /**
     * 获取最后插入id
     * 
     * @return string
     */
    public function getInsertId() {
        return $this->_insertId;
    }

    /**
     * 执行sql语句
     * 
     * @param string $sql sql语句
     * @param boolean $toArray 是否转为数组
     * @return array|object
     */
    public function query($sql, $toArray = false) {
        if (!$this->_adapter) {
            $this->setScheme($this->_defaultScheme);
        }
        $sql = $this->replacePre($sql);
        $this->_lastSql = $sql;
        $isDebug = Debug::check();
        $isDebug && Debug::setSql($sql);
        $rs = $this->_adapter->query($sql);
        $this->setError();
        $isDebug && Debug::getTime();
        if (true === $toArray) {
            $rs = $this->toArray($rs);
        }
        if ($this->_reset) {
            $this->reset();
        }
        return $rs;
    }

    /**
     * 开启事务
     */
    public function begin() {
        $this->query('BEGIN');
    }

    /**
     * 回滚
     */
    public function rollback() {
        $this->query('ROLLBACK');
    }

    /**
     * 提交
     */
    public function commit() {
        $this->query('COMMIT');
    }

    /**
     * 获取sql版本
     * 
     * @return string
     */
    public function getServerInfo() {
        return $this->_adapter->version();
    }

    /**
     * 抛出异常方法,便于移植
     * 
     * @param string $errorString 错误信息
     */
    public function throwError($errorString) {
        thrower($errorString);
    }

    /**
     * 替换前缀
     * 
     * @param string $sql sql语句
     * @return string
     */
    private function replacePre($sql) {
        return str_replace($this->_pre, $this->_adapter->table_prefix, $sql);
    }

    /**
     * 设置库
     * 
     * @param string $key 库名
     * @return \Library\Db\Db
     */
    private function autoScheme($key) {
        if (($this->_currentScheme === $this->_defaultScheme && $this->_autoScheme) || !$this->_currentScheme) {
            $this->setScheme($key);
        }
        return $this;
    }

    /**
     * 解析from语句
     */
    private function parseFrom() {
        $from = '';
        if (is_array($this->_from)) {
            $from = sprintf('`%s` AS `%s`', ($this->_pre . current($this->_from)), key($this->_from));
            $this->_table = key($this->_from);
        } else {
            $from .= sprintf('`%s`', ($this->_pre . $this->_from));
            $this->_table = $this->_pre . $this->_from;
        }
        return $from;
    }

    /**
     * 解析columns语句
     */
    private function parseColumns() {
        $columns = '';
        if (empty($this->_columns)) {
            $columns .= '*';
        } else {
            if (!is_array($this->_columns)) {
                $columns .= $this->_columns;
            } else {
                foreach ($this->_columns as $as => $column) {
                    if (is_int($as)) {
                        $columns .= sprintf('`%s`,', $column);
                    } else {
                        if (false !== strpos($column, '.')) {
                            $ex = explode('.', $column);
                            $column = sprintf('`%s`.`%s`', $ex[0], $ex[1]);
                        } else {
                            $column = sprintf('`%s`', $column);
                        }
                        $columns .= sprintf('%s AS `$s`,', $column, $as);
                    }
                }
                $columns = substr($columns, 0, -1);
            }
        }
        return $columns;
    }

    /**
     * 解析join语句
     */
    private function parseJoin(&$columns = null) {
        $join = '';
        $joinTable = '';
        if (!empty($this->_join)) {
            if ($columns !== null) {
                $currentCols = explode(',', $columns);
                $columns = sprintf('`%s`.%s', $this->_table, implode(sprintf(',`%s`.', $this->_table), $currentCols));
            }
            foreach ($this->_join as $joins) {
                $table = array();
                if (is_array($joins['join'])) {
                    $join .= ' ' . $joins['type'] . ' ';
                    $key = key($joins['join']);
                    $join .= is_int($key) ?
                            '`' . $this->_pre . current($joins['join']) . '`' :
                            sprintf('`%s` AS `%s`', ($this->_pre . current($joins['join'])), $key);
                    $joinTable = is_int($key) ? $this->_pre . $joins['join'] : $key;
                    $on = explode('=', $joins['condition']);
                    foreach ($on as $value) {
                        if (strpos($value, '.')) {
                            $ex = explode('.', $value);
                            $table[] = sprintf('`%s`.`%s`', $ex[0], $ex[1]);
                        }
                    }
                    $join .= sprintf(' ON %s = %s', $table[0], $table[1]);
                    if (null !== $columns) {
                        if ($joins['columns'] === '*') {
                            $columns .= ',`' . $joinTable . '`.' . $joins['columns'];
                        } else {
                            if (is_array($joins['columns'])) {
                                foreach ($joins['columns'] as $as => $column) {
                                    $columns .= is_int($as) ?
                                            sprintf(',`%s`.%s', $joinTable, $column) :
                                            sprintf(',`%s`.`%s` AS `%s`', $joinTable, $column, $as);
                                }
                            } else {
                                if (strpos($joins['columns'], ',')) {
                                    $exColumns = explode(',', $joins['columns']);
                                    foreach ($exColumns as $column) {
                                        $columns .= sprintf(',`%s`.`%s`', $joinTable, $column);
                                    }
                                } else {
                                    $columns .= sprintf(',`%s`.`%s`', $joinTable, $joins['columns']);
                                }
                            }
                        }
                    }
                } else {
                    $join .= ' ' . $joins['join'];
                }
            }
        }
        return $join;
    }

    /**
     * 解析where语句
     */
    private function parseWhere($target = '_where') {
        $where = '';
        if (!empty($this->{$target})) {
            $where .= ' ' . strtoupper(ltrim($target, '_')) . ' ';
            foreach ($this->{$target} as $num => $wheres) {
                if ($num !== 0) {
                    $where .= ' ' . $wheres['link'] . ' ';
                }
                if (!is_array($wheres['where'])) {
                    $currentWhere = $wheres['where'];
                } else {
                    $currentWhere = '';
                    $firstKey = key($wheres['where']);
                    foreach ($wheres['where'] as $key => $value) {
                        if ($firstKey !== $key) {
                            $currentWhere .= ' AND ';
                        }
                        if (is_int($key)) {
                            $currentWhere .= $value;
                        } else {
                            if (strpos($key, '.')) {
                                $ex = explode('.', $key);
                                $key = sprintf('`%s`.`%s`', $ex[0], $ex[1]);
                            } else {
                                $key = '`' . $key . '`';
                            }
                            $currentWhere .= $key . (is_array($value) ?
                                            (sprintf(" IN ('%s')", implode("','", $value))) :
                                            (sprintf(" = '%s'", $value)));
                        }
                    }
                }
                $where .= true === $wheres['predicate'] ? '(' . $currentWhere . ')' : $currentWhere;
            }
        }
        return $where;
    }

    /**
     * 解析group语句
     */
    private function parseGroup() {
        $group = '';
        if (!empty($this->_group)) {
            $group .= ' GROUP BY ';
            $explode = is_array($this->_group) ? $this->_group : explode(',', $this->_group);
            foreach ($this->_group as $value) {
                $explode = is_array($value) ? $value : explode(',', $value);
                foreach ($explode as $value) {
                    if (false !== strpos($value, '.')) {
                        $ex = explode('.', $value);
                        $group .= sprintf('`%s`.`%s`,', $ex[0], $ex[1]);
                    } else {
                        $group .= '`' . $value . '`,';
                    }
                }
            }
            $group = substr($group, 0, -1);
        }
        return $group;
    }

    /**
     * 解析order语句
     */
    private function parseOrder() {
        $order = '';
        if (!empty($this->_order)) {
            $order .= ' ORDER BY ';
            foreach ($this->_order as $key => $value) {
                if (false !== strpos($value, ' ')) {
                    $ex = explode(' ', $value);
                    $value = array($ex[0] => $ex[1]);
                }
                if (is_array($value)) {
                    $zoom = key($value);
                    $key = $value[$zoom];
                    $value = $zoom;
                }
                if (strpos($value, '.')) {
                    $ex = explode('.', $value);
                    $value = sprintf('`%s`.`%s`', $ex[0], $ex[1]);
                } else {
                    $value = '`' . $value . '`';
                }
                $order .= is_int($key) ? $value : ($value . ' ' . $key);
            }
        }
        return $order;
    }

    /**
     * 解析limit语句
     */
    private function parseLimit() {
        $limit = '';
        if ('' !== $this->_limit) {
            $limit .= ' LIMIT ' . $this->_limit;
        }
        return $limit;
    }

    /**
     * 解析offset语句
     */
    private function parseOffset() {
        $offset = '';
        if ('' !== $this->_offset) {
            $offset .= ' OFFSET ' . $this->_offset;
        }
        return $offset;
    }

    /**
     * 设置错误
     */
    private function setError() {
        $this->_error = array(
            'error' => (boolean) $this->_adapter->error(),
            'code' => $this->_adapter->errno(),
            'message' => $this->_adapter->error()
        );
    }

}