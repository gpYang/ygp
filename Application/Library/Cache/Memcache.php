<?php

/**
 * memcache缓存类库
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Cache
 */

namespace Library\Cache;

use Library\Cache\CacheInterface;

/**
 * @package Library.Cache
 */
class Memcache implements CacheInterface {

    /**
     * @var object 缓存对象
     */
    private static $_memcacheHandler = null;

    /**
     * @var array 缓存配置
     */
    private $_memcacheConfig = array();

    /**
     * @var string 存数据缓存的键
     */
    private static $_listNameTemplate = '%s*List';

    /**
     * @var null|string 系统标示
     */
    private static $_appkey = null;

    /**
     * @var type 是否启用压缩
     */
    private static $_flag = 0;

    /**
     * 构造函数
     */
    public function __construct() {
        if (!isset(self::$_appkey)) {
            self::setAppkey(($appkey = config('app_key')) ? $appkey : '');
        }
        if (!self::$_memcacheHandler) {
            self::$_memcacheHandler = $this->connect();
        }
    }

    public function __destruct() {
        $this->close();
    }

    /**
     * 设置系统appkey
     * 
     * @param string $appkey
     */
    public static function setAppkey($appkey) {
        self::$_appkey = $appkey;
        self::$_listNameTemplate = sprintf(self::$_listNameTemplate, $appkey);
    }

    /**
     * 设置是否启用压缩
     * 
     * @param int $flag
     */
    public static function setFlag($flag) {
        self::$_flag = $flag;
    }

    /**
     * 连接memcache
     * 
     * @return \Memcache
     */
    private function connect() {
        if (!class_exists('Memcache') || !function_exists('memcache_connect')) {
            $this->throwError('无法加载memcahce扩展');
        }
        $handle = new \Memcache;
        empty($this->_memcacheConfig) && $this->_memcacheConfig = config('memcache');

        $handle->connect($this->_memcacheConfig['host'], $this->_memcacheConfig['port']);
        if (!$handle->getServerStatus($this->_memcacheConfig['host'], $this->_memcacheConfig['port'])) {
            $this->throwError('memcache服务器无法连接');
        }
        return $handle;
    }

    /**
     * 存缓存
     * 
     * @param string $key 缓存键
     * @param mixed $val 缓存值
     * @param int $flag 是否压缩
     * @param int $expire 失效时间,秒或时间戳
     * @return mixed
     */
    public function set($key, $val, $expire = 0) {
        if (!$this->checkKey($key, array('*'))) {
            return false;
        }
        $list = self::$_memcacheHandler->get(self::$_listNameTemplate);
        $key = self::$_appkey . '@' . $key;
        if (empty($list)) {
            $list = array();
        }
        $list[$key] = time();
        self::$_memcacheHandler->set(self::$_listNameTemplate, $list, self::$_flag, 0);
        return self::$_memcacheHandler->set($key, $val, self::$_flag, $expire);
    }

    /**
     * 取缓存
     * @example 
     * input 'aaaa' = 'aacc' = 'aaaa/a' = 'aaaa/b' = 'b'
     * get('aa*') -> array('aaaa' => 'b', 'aacc' => 'b')
     * get('aaaa/*') -> array('aaaa/a' => 'b', 'aaaa/b' => 'b')
     * get(array('aaaa', 'aaaa/a'))
     * 
     * @param string|array $key 缓存的键
     * @return mixed
     */
    public function get($key) {
        if (!$this->checkKey($key)) {
            return false;
        }
        $list = self::$_memcacheHandler->get(self::$_listNameTemplate);
        if (empty($list)) {
            return false;
        }
        if (is_array($key)) {
            $keys = array();
            foreach ($key as $value) {
                $keys[] = self::$_appkey . '@' . $value;
            }
            $result = self::$_memcacheHandler->get($keys);
        } else {
            $matches = $this->getDataFromStringKey($key, $list);
            if (!empty($matches)) {
                $result = self::$_memcacheHandler->get($matches);
            }
        }
        if (!empty($result)) {
            $return = $this->getListExpire($result, $list);
            if (!empty($return)) {
                return (!is_array($key) && false === strpos($key, '*')) ? current($return) : $return;
            }
        }
        return false;
    }

    /**
     * 取系统下所有缓存数据
     * 
     * @return mixed
     */
    public function getAll() {
        $allKey = self::$_memcacheHandler->get(self::$_listNameTemplate);
        if (!empty($allKey)) {
            $allKey = self::$_memcacheHandler->get(array_keys($allKey));
            return $this->getListExpire($allKey);
        }
        return $allKey;
    }

    /**
     * 替换已存在的缓存数据,不存在返回false
     * 
     * @param string $key 缓存键
     * @param mixed $val 缓存值
     * @param int $flag 是否压缩
     * @param int $expire 失效时间,秒或时间戳
     * @return boolean
     */
    public function replace($key, $val, $expire = 0) {
        if (!$this->checkKey($key, array('*'))) {
            return false;
        }
        $key = self::$_appkey . '@' . $key;
        return self::$_memcacheHandler->replace($key, $val, self::$_flag, $expire);
    }

    /**
     * 删除缓存数据,支持*和/
     * 
     * @param string $key 缓存键
     * @param int $timeout 延时
     * @return boolean
     */
    public function delete($key, $timeout = 0) {
        if (!$this->checkKey($key)) {
            return false;
        }
        $list = self::$_memcacheHandler->get(self::$_listNameTemplate);
        if (empty($list)) {
            return true;
        }
        $result = $this->getDataFromStringKey($key, $list);
        if (!empty($result)) {
            foreach ($result as $value) {
                self::$_memcacheHandler->delete($value, $timeout);
                unset($list[$value]);
            }
        }
        empty($list) ?
                        self::$_memcacheHandler->delete(self::$_listNameTemplate, $timeout) :
                        self::$_memcacheHandler->set(self::$_listNameTemplate, $list, self::$_flag, 0);
        return true;
    }

    /**
     * 清除系统下所有缓存
     * 
     * @return boolean
     */
    public function flush() {
        $list = self::$_memcacheHandler->get(self::$_listNameTemplate);
        if (!empty($list)) {
            foreach ($list as $key => $value) {
                self::$_memcacheHandler->delete($key);
            }
            self::$_memcacheHandler->delete(self::$_listNameTemplate);
        }
        return true;
    }

    /**
     * 增加缓存值,原值不为数字返回false
     * 
     * @param string $key 缓存键
     * @param int $val 增加数
     * @return boolean
     */
    public function increment($key, $val = 1) {
        if (!$this->checkKey($key, array('*'))) {
            return false;
        }
        $key = self::$_appkey . '@' . $key;
        return self::$_memcacheHandler->increment($key, $val);
    }

    /**
     * 减少缓存值,原值不为数字返回false
     * 
     * @param string $key 缓存键
     * @param int $val 减少数
     * @return boolean
     */
    public function decrement($key, $val = 1) {
        if (!$this->checkKey($key, array('*'))) {
            return false;
        }
        $key = self::$_appkey . '@' . $key;
        return self::$_memcacheHandler->decrement($key, $val);
    }

    /**
     * 添加缓存数据,已存在返回false
     * 
     * @param string $key 缓存键
     * @param mixed $val 缓存值
     * @param int $flag 是否压缩
     * @param int $expire 失效时间,秒或时间戳
     * @return boolean
     */
    public function add($key, $val, $expire = 0) {
        if (!$this->checkKey($key, array('*'))) {
            return false;
        }
        $list = self::$_memcacheHandler->get(self::$_listNameTemplate);
        if (empty($list)) {
            $list = array();
        }
        $list[$key] = time();
        $key = self::$_appkey . '@' . $key;
        if (self::$_memcacheHandler->add($key, $val, self::$_flag, $expire)) {
            self::$_memcacheHandler->set(self::$_listNameTemplate, $list, self::$_flag, 0);
            return true;
        }
        return false;
    }

    /**
     * 关闭服务器连接
     * 
     * @return boolean
     */
    public function close() {
        return self::$_memcacheHandler->close();
    }

    /**
     * 获取通过*号匹配的数据
     * 
     * @param string $key 键
     * @param array $list 数据
     * @return array
     */
    private function getDataFromStringKey(string $key, $list) {
        $result = array();
        if (false === strpos($key, '*')) {
            $result[] = self::$_appkey . '@' . $key;
        } else {
            if ($key === '*') {
                return array_keys($list);
            }
            $pattern = self::$_appkey . '@' . preg_replace('/(\\\\\*)+/', '(?:[^\/]*?)', preg_quote($key, '/'));
            $result = preg_grep("/^{$pattern}$/", array_keys($list));
        }
        return $result;
    }

    /**
     * 判断列表中有无已经过期的数据
     * 
     * @param array $data 获取到的数据
     * @param array $list 总的数据,当为空时就是$data
     * @return array
     */
    private function getListExpire($data, $list = array()) {
        $finalList = empty($list) ? $data : $list;
        $result = array();
        foreach ($data as $key => $value) {
            if (false === $value) {
                unset($finalList[$key]);
            } else {
                $result[str_replace(self::$_appkey . '@', '', $key)] = $value;
            }
        }
        self::$_memcacheHandler->set(self::$_listNameTemplate, $finalList, self::$_flag, 0);
        return $result;
    }

    /**
     * 检查key的合法性
     * 
     * @param string|array $key 键名
     * @param array $sensitive 敏感字符
     * @return boolean
     */
    private function checkKey($key, $sensitive = array()) {
        if (empty($key)) {
            return false;
        }
        if (!empty($sensitive)) {
            if (is_array($key)) {
                foreach ($key as $k) {
                    if (!$this->checkKeyRecursive($k, $sensitive)) {
                        return false;
                    }
                }
            } else {
                return $this->checkKeyRecursive($key, $sensitive);
            }
        }
        return true;
    }

    /**
     * 递归检查key的合法性
     * 
     * @param string $key
     * @param array $sensitive
     * @return boolean
     */
    private function checkKeyRecursive($key, $sensitive) {
        foreach ($sensitive as $value) {
            if (strpos($key, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 抛出异常方法,便于移植
     * 
     * @param string $errorString 错误信息
     */
    public function throwError($errorString) {
        thrower($errorString);
    }

}

?>