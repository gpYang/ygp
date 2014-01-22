<?php

/**
 * 缓存操作类接口
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Cache
 */

namespace Library\Cache;

/**
 * @package Library.Cache
 */
interface CacheInterface {

    public function set($key, $val);

    public function get($key);

    public function getAll();

    public function delete($key);

    public function flush();
}

?>
