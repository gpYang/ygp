<?php

/**
 * 简体中文
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Common.Config
 */
return array(
    //验证提示::邮件
    'validator/emailAddressInvalid' => '无效的类型',
    'validator/emailAddressInvalidFormat' => '不是有效的电子邮件地址',
    'validator/emailAddressInvalidHostname' => '\'%hostname%\' 不是有效的电子邮件地址的主机名',
    'validator/emailAddressInvalidMxRecord' => '\'%hostname%\' 没有任何有效的电子邮件地址的MX或A记录',
    'validator/emailAddressInvalidSegment' => '\'%hostname%\' 不是在一个可路由的网段中',
    'validator/emailAddressDotAtom' => '不是有效的电子邮件地址',
    'validator/emailAddressQuotedString' => '不是有效的电子邮件地址',
    'validator/emailAddressInvalidLocalPart' => '不是有效的电子邮件地址',
    'validator/emailAddressLengthExceeded' => '输入超过允许的长度',
    //验证提示::是否是数字
    'validator/notDigits' => '%key% 必须为数字',
    'validator/digitsStringEmpty' => '%key% 不允许为空',
    //验证提示::是否为空
    'validator/isEmpty' => '%key% 必须为空',
    //验证提示::为空
    'validator/notEmpty' => '%key% 为空',
    //验证提示::类型
    'validator/digitsInvalid' => '%key% 只允许是字符串,整型或浮点型',
    //验证提示::常用字母与数字
    'validator/alnum' => '%key% 含有不允许的字符',
    //验证提示::是否相等
    'validator/isEqual' => '%key%不等于%value%',
    //验证提示::是否不相等
    'validator/notEqual' => '%key%等于%value%',
    //验证提示::是否小于
    'validator/lt' => '%key%大于%value%',
    //验证提示::是否大于
     'validator/gt' => '%key%小于%value%',
    //验证提示::是否在范围内
     'validator/between' => '%key%不在%value%范围内',
    //验证提示::是否整型
    'validator/int' => '%key%不是整型',
    //验证提示::是否浮点型
    'validator/float' => '%key%不是浮点型',
    //验证域名
    'validator/hostname' => '%hostname%不是有效域名',
    //验证16进制
    'validator/hex' => '%s%不是16进制',
    //验证信用卡
    'validator/creditcardLength' => '%key%不是有效信用卡号',
    'validator/creditcardPrefix' => '',
    //验证日期
    'validator/dateInvalid' => '%key%不是有效日期',
    'validator/dateFalseFormat' => '%key%不是有效日期',
    'validator/dateInvalidDate' => '%key%不是有效日期',
     //验证手机
    'validator/mobile' => '%key%不是有效手机号码',
     //验证ip
    'validator/ip' => '%key%不是有效ip地址',
     //验证ip
    'validator/length' => '超过了%key%限制的长度%value%',
     //验证id
    'validator/id' => '%key%不是有效身份证号码',
     //验证邮政编码
    'validator/postCode' => '%key%不是有效邮政编码',
    //验证简体中文
    'validator/zh_cn' => '%value%不是简体中文',
);