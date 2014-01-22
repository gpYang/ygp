<?php

/**
 * request配置文件(可对请求数据进行处理)
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Common.Config
 */
return array(
    'server' => function ($data) {
        unset($data['REQUEST_TIME']);
        return $data;
    },
    'get' => function ($data) {
        return $data;
    }
);
?>
