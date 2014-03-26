<?php

/**
 * 文件缓存类库
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Cache
 */

namespace Library\Cache;

use Library\Cache\CacheInterface;

/**
 * @package Library.Cache
 */
class FileCache implements CacheInterface {

    /**
     * @var string 存数据缓存的键
     */
    private static $_listNameTemplate = '%s*List';

    /**
     * @var string 系统标示
     */
    private static $_appkey = null;

    /**
     * @var string 缓存路径
     */
    private static $_cachePath = '';

    /**
     * 设置缓存路径
     * 
     * @param string $path
     * @param string $appkey
     */
    public static function setCachePath($path, $appkey) {
        self::$_appkey = $appkey;
        self::$_cachePath = $path . '/' . sha1(self::$_appkey);
        self::$_listNameTemplate = sprintf(self::$_listNameTemplate, $appkey);
    }

    /**
     * 写入缓存
     *
     * @param string $key 缓存key
     * @param string $value 缓存value
     * @param string $t 缓存时间 单位秒
     * @param string $d 缓存文件目录
     * @return bool 成功返回true 失败返回false
     */
    public function set($key, $value, $expire = 0) {
        if (!$this->checkKey($key, array('*'))) {
            return false;
        }
        //开辟一个新键，记录当前键名
        $list = $this->getFile(self::$_listNameTemplate);
        if (empty($list)) {
            $list = array();
        }
        $list[$key] = time();
        $this->setFile(self::$_listNameTemplate, $list, 0);
        return $this->setFile($key, $value, $expire);
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
        $list = $this->getFile(self::$_listNameTemplate);
        if (empty($list)) {
            return false;
        }
        $result = $keys = array();
        $keys = is_array($key) ? $key : $this->getDataFromStringKey($key, $list);
        if (!empty($keys)) {
            foreach ($keys as $value) {
                (false !== ($data = $this->getFile($value))) && $result[$value] = $data;
            }
        }
        if (!empty($result)) {
            return (!is_array($key) && false === strpos($key, '*')) ? current($result) : $result;
        }
        return false;
    }

    /**
     * 取系统下所有缓存数据
     * 
     * @return mixed
     */
    public function getAll() {
        $list = $this->getFile(self::$_listNameTemplate);
        if (empty($list)) {
            return false;
        }
        $result = array();
        foreach ($list as $k => $v) {
            (false !== ($data = $this->getFile($k))) && $result[$k] = $data;
        }
        return empty($result) ? false : $result;
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
        $list = $this->getFile(self::$_listNameTemplate);
        if (empty($list)) {
            return true;
        }
        $result = $this->getDataFromStringKey($key, $list);
        if (!empty($result)) {
            foreach ($result as $value) {
                $this->isUnlink($value, $timeout);
                unset($list[$value]);
            }
        }
        empty($list) ?
                        $this->isUnlink(self::$_listNameTemplate, $timeout) :
                        $this->setFile(self::$_listNameTemplate, $list, 0);
        return true;
    }

    /**
     * 清除系统下所有缓存
     * 
     * @return boolean
     */
    public function flush() {
        return $this->deldir(self::$_cachePath);
    }

    /**
     * 删除目录下所有文件和文件夹
     * 
     * @param string $dir 目录
     * @return boolean
     */
    private function deldir($dir) {
        $dh = opendir($dir);
        while ($file = readdir($dh)) {
            if ($file != "." && $file != "..") {
                $fullpath = $dir . "/" . $file;
                if (!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    $this->deldir($fullpath);
                }
            }
        }
        closedir($dh);
        return rmdir($dir);
    }

    /**
     * 是否已删除文件
     * 
     * @param string $key 缓存键
     * @param int $timeout 延时
     * @return boolean
     */
    private function isUnlink($key, $timeout) {
        $filename = $this->getFilename($key);
        if (!file_exists($filename)) {
            return true;
        }
        if ($timeout !== 0) {
            $data = $this->getFile($key);
            return $this->setFile($key, $data, $timeout);
        }
        return unlink($filename);
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
            $result[] = $key;
        } else {
            if ($key === '*') {
                return array_keys($list);
            }
            $pattern = preg_replace('/(\\\\\*)+/', '(?:[^\/]*?)', preg_quote($key, '/'));
            $result = preg_grep("/^{$pattern}$/", array_keys($list));
        }
        return $result;
    }

    /**
     * 设置文件
     * 
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return boolean
     */
    private function setFile($key, $value, $expire) {
        $filename = $this->getFilename($key);
        if (!$this->isMkdir(dirname($filename))) {
            return false;
        }
        $data['time'] = $expire === 0 ? 0 : time() + $expire;
        $data['data'] = $value;
        $data = serialize($data);
        return file_put_contents($filename, $data);
    }

    /**
     * 创建目录
     *
     * @param string $dir
     * @return bool 成功返回 true 失败返回 false
     */
    private function isMkdir($dir = '') {
        if (empty($dir)) {
            return false;
        }
        if (!is_writable($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取文件
     * 
     * @param string $key
     * @return boolean
     */
    private function getFile($key) {
        $filename = $this->getFilename($key);
        if (!file_exists($filename)) {
            return false;
        }
        $data = unserialize(file_get_contents($filename));
        if ($data['time'] !== 0 && $data['time'] < time()) {
            $this->delete($key);
            return false;
        }
        return $data['data'];
    }

    /**
     * 获取文件名
     * 
     * @param string $key
     * @return string
     */
    private function getFilename($key) {
        $hashKey = sha1($key);
        return self::$_cachePath . '/' . substr($hashKey, 0, 2) . '/' . substr($hashKey, 2, 2) . '/' . $hashKey;
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

}

?>