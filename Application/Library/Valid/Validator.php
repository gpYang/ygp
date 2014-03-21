<?php

/**
 * 验证类，常用于验证表单数据
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Valid
 */

namespace Library;

/**
 * @package Library.Valid
 */
class Validator {

    protected $validPrefix = 'validator';
    protected $key;
    private $messages = array();
    private static $dictionary = array();

    public function __construct($key = null) {
        if (isset($key)) {
            $this->setKey($key);
        }
    }

    public static function setDictionary(array $dictionary) {
        self::$dictionary = $dictionary;
    }

    public function setKey($key) {
        $this->key = $key;
    }

    public function clearMessage() {
        $this->messages = array();
    }

    public function getMessage() {
        return $this->messages;
    }

    public function setMessage(string $message, $value = '') {
        if (0 === strpos($message, '@')) {
            $message = substr($message, 1);
            if (isset(self::$dictionary[$this->validPrefix . '/' . $message])) {
                $message = self::$dictionary[$this->validPrefix . '/' . $message];
            } else {
                $message = $this->validPrefix . '/' . $message;
            }
        }
        $this->messages[] = str_replace(array("%key%", "%value%"), array($this->key, $value), $message);
    }

    /**
     * 验证普通字符与数字组合
     */
    public function alnum($value, $message = '') {
        $pattern = '/^[a-zA-Z0-9]$/';
        if (preg_match($pattern, $value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@alnum' : $message);
        return false;
    }

    /**
     * 验证相等
     */
    public function isEqual($value, $condition, $message = '') {
        if ($value == $condition) {
            return true;
        }
        $this->setMessage(empty($message) ? '@isEqual' : $message, $condition);
        return false;
    }

    /**
     * 验证不相等
     */
    public function notEqual($value, $condition, $message = '') {
        if ($value != $condition) {
            return true;
        }
        $this->setMessage(empty($message) ? '@notEqual' : $message, $condition);
        return false;
    }

    /**
     * 是否小于
     */
    public function lt($value, $condition, $message = '') {
        if ($value < $condition) {
            return true;
        }
        $this->setMessage(empty($message) ? '@lt' : $message, $condition);
        return false;
    }

    /**
     * 是否大于
     */
    public function gt($value, $condition, $message = '') {
        if ($value > $condition) {
            return true;
        }
        $this->setMessage(empty($message) ? '@gt' : $message, $condition);
        return false;
    }

    /**
     * 是否在数值范围
     * @param number $value 
     * @param array $condition 
     */
    public function between($value, array $condition, $message = '') {
        if (($value >= $condition[0]) && ($value <= $condition[1])) {
            return true;
        }
        $this->setMessage(empty($message) ? '@between' : $message, implode("~", $condition));
        return false;
    }

    /**
     * 是否为数字
     */
    public function number($value, $message = "") {
        if (is_numeric($value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@notDigits' : $message);
        return false;
    }

    /**
     * 是否为整型
     */
    public function int($value, $message = "") {
        if (is_numeric($value) && intval($value) == $value) {
            return true;
        }
        $this->setMessage(empty($message) ? '@int' : $message);
        return false;
    }

    /**
     * 是否是简体中文
     */
    public function zh_cn($value, $message = "") {
        $pattern = "/^[\x80-\xff]+$/";
        if (preg_match($pattern, $value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@zh_cn' : $message);
        return false;
    }

    /**
     * 是否是繁体中文（香港）
     */
    public function zh_hk() {
        return true;
    }

    /**
     * 是否为浮点型
     */
    public function float($value, $message = '') {
        $pattern = "/^(-?\d+)(\.\d+)?$/";
        if (preg_match($pattern, $value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@float' : $message);
        return false;
    }

    /**
     * 验证手机号码
     */
    public function mobile($value, $message = '') {
        $pattern = "/^1([358]\d|4[57])\d{8}$/";
        if (preg_match($pattern, $value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@mobile' : $message);
        return false;
    }

    /**
     * 验证是否为空
     */
    public function isEmpty($value, $message = '') {
        if (empty($value) && $value !== '0') {
            return true;
        }
        $this->setMessage(empty($message) ? '@isEmpty' : $message);
        return false;
    }

    /**
     * 验证是否不为空
     */
    public function notEmpty($value, $message = '') {
        if (!empty($value) || $value === '0') {
            return true;
        }
        $this->setMessage(empty($message) ? '@notEmpty' : $message);
        return false;
    }

    /**
     * 验证ip
     */
    public function ip($value, $message = '') {
        $pattern = "/^(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})(\.(25[0-5]|2[0-4]\d|1\d{2}|\d{1,2})){3}$/";
        if (preg_match($pattern, $value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@ip' : $message);
        return false;
    }

    /**
     * 验证长度
     */
    public function length($value, $condition, $message = '') {
        $msg = null;
        $length = iconv_strlen($value, 'UTF-8');
        if (is_array($condition)) {
            if (count($condition) > 1 && isset($condition[1]) && is_numeric($condition[1])) {
                if ($length >= $condition[0] && $length <= $condition[1]) {
                    return true;
                } else {
                    $msg = implode("~", $condition);
                }
            } else {
                if ($length <= $condition[0]) {
                    return true;
                } else {
                    $msg = $condition[0];
                }
            }
        } else {
            if ($length <= $condition) {
                return true;
            } else {
                $msg = $condition;
            }
        }
        $this->setMessage(empty($message) ? '@length' : $message, $msg);
        return false;
    }

    /**
     * 验证身份证
     */
    public function id($value, $message = '') {
        if (IDCard::isCard($value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@id' : $message);
        return false;
    }

    /**
     * 验证邮编
     */
    public function postCode($value, $message = '') {
        $pattern = "/^[1-9]\d{5}$/";
        if (preg_match($pattern, $value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@postCode' : $msg);
        return false;
    }

    /**
     * 验证邮箱
     */
    public function email() {
        $validator = new ZV\EmailAddress();
        return call_user_func_array(array($this, 'ZendValid'), array_merge(array($validator), func_get_args()));
    }

    /**
     * 验证日期 诸如2012-12-22
     */
    public function date($value) {
        if ($value === '0000-00-00') {
            return true;
        }
        $validator = new ZV\Date(array('format' => 'Y-m-d'));
        return call_user_func_array(array($this, 'ZendValid'), array_merge(array($validator), func_get_args()));
    }

    /**
     * 验证时间 诸如2012-12-22 12:12:12
     * @param type $value
     */
    public function datetime($value) {
        $validator = new ZV\Date(array('format' => 'Y-m-d H:i:s'));
        return call_user_func_array(array($this, 'ZendValid'), array_merge(array($validator), func_get_args()));
    }

    /**
     * 是否为信用卡
     */
    public function creditCard($value) {
        $validator = new ZV\creditCard();
        return call_user_func_array(array($this, 'ZendValid'), array_merge(array($validator), func_get_args()));
    }

    /**
     * 是否为16进制
     */
    public function hex($value) {
        $validator = new ZV\Hex();
        return call_user_func_array(array($this, 'ZendValid'), array_merge(array($validator), func_get_args()));
    }

    /**
     * 验证主机名
     */
    public function hostname($value) {
        $validator = new ZV\HostName();
        return call_user_func_array(array($this, 'ZendValid'), array_merge(array($validator), func_get_args()));
    }

}