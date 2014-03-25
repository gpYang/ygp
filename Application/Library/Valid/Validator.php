<?php

/**
 * 验证类，常用于验证表单数据
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library.Valid
 */

namespace Library\Valid;

/**
 * @package Library.Valid
 */
class Validator {

    protected $validPrefix = 'validator';
    protected $key;
    private $messages = array();
    private static $dictionary = array();

    /**
     * 设置错误信息字典
     * 
     * @param array $dictionary
     */
    public static function setDictionary(array $dictionary) {
        self::$dictionary = $dictionary;
    }

    public function __construct($key = null) {
        if (isset($key)) {
            $this->setKey($key);
        }
    }

    /**
     * 设置验证名
     * 
     * @param type $key
     */
    public function setKey($key) {
        $this->key = $key;
    }

    /**
     * 清除所有错误信息
     */
    public function clearMessage() {
        $this->messages = array();
    }

    /**
     * 获取所有错误信息
     * 
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * 保存错误信息
     * 
     * @param string $message 错误信息
     * @param string $value 错误数据
     */
    public function setMessage(string $message, $value = '') {
        if (0 === strpos($message, '@')) {
            $message = substr($message, 1);
            if (isset(self::$dictionary[$this->validPrefix . '/' . $message])) {
                $message = self::$dictionary[$this->validPrefix . '/' . $message];
            } else {
                $message = $this->validPrefix . '/' . $message;
            }
        }
        $this->messages[$this->key] = str_replace(array("%key%", "%value%"), array($this->key, $value), $message);
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
        $this->setMessage(empty($message) ? '@notDigits' : $message, $value);
        return false;
    }

    /**
     * 是否为整型
     */
    public function int($value, $message = "") {
        if (is_numeric($value) && intval($value) == $value) {
            return true;
        }
        $this->setMessage(empty($message) ? '@int' : $message, $value);
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
        $this->setMessage(empty($message) ? '@zh_cn' : $message, $value);
        return false;
    }

    /**
     * 是否为浮点型
     */
    public function float($value, $message = '') {
        $pattern = "/^(-?\d+)(\.\d+)?$/";
        if (preg_match($pattern, $value)) {
            return true;
        }
        $this->setMessage(empty($message) ? '@float' : $message, $value);
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
        $this->setMessage(empty($message) ? '@mobile' : $message, $value);
        return false;
    }

    /**
     * 验证是否为空
     */
    public function isEmpty($value, $message = '') {
        if (empty($value) && $value !== '0') {
            return true;
        }
        $this->setMessage(empty($message) ? '@isEmpty' : $message, $value);
        return false;
    }

    /**
     * 验证是否不为空
     */
    public function notEmpty($value, $message = '') {
        if (!empty($value) || $value === '0') {
            return true;
        }
        $this->setMessage(empty($message) ? '@notEmpty' : $message, $value);
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
        $this->setMessage(empty($message) ? '@ip' : $message, $value);
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
        $this->setMessage(empty($message) ? '@id' : $message, $value);
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
        $this->setMessage(empty($message) ? '@postCode' : $message, $value);
        return false;
    }

    /**
     * 验证邮箱
     */
    public function email($value, $message = '') {
        $result = true;
        //首先判断@只能有1个,且邮箱名不能超过64位,域名不能超过255位
        if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $value)) {
            $result = false;
        }
        $email_array = explode('@', $value);
        $local_array = explode('.', $email_array[0]);
        foreach ($local_array as $local) {
            if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local)) {
                $result = false;
            }
        }

        //判断域名是否ip
        if (!preg_match('/^\[?[0-9\.]+\]?$/', $email_array[1])) {
            $domain_array = explode('.', $email_array[1]);
            //有无小数点
            if (count($domain_array) < 2) {
                $result = false;
            }
            foreach ($domain_array as $domain) {
                if (!preg_match('/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/', $domain)) {
                    $result = false;
                }
            }
        }
        if (!$result) {
            $this->setMessage(empty($message) ? '@email' : $message, $value);
        }
        return $result;
    }

    /**
     * 验证时间格式 诸如2012-12-22
     */
    public function datetime($value, $format = 'Y-m-d', $message = '') {
        $result = true;
        $strArr = explode('-', $value);
        if (empty($strArr)) {
            $result = false;
        }
        foreach ($strArr as $val) {
            if (strlen($val) < 2) {
                $val = '0' . $val;
            }
            $newArr[] = $val;
        }
        $str = implode('-', $newArr);
        $unixTime = strtotime($str);
        $checkDate = date($format, $unixTime);
        if ($checkDate != $str) {
            $result = false;
        }
        if (!$result) {
            $this->setMessage(empty($message) ? '@date' : $message, $value);
        }
        return $result;
    }

    /**
     * 是否为信用卡
     */
    public function creditCard($value, $message = '') {
        $result = true;
        $cardnumber = preg_replace('/\D|\s/', '', $value);
        $cardlength = strlen($cardnumber);
        if ($cardlength != 0) {
            $parity = $cardlength % 2;
            $sum = 0;
            for ($i = 0; $i < $cardlength; $i++) {
                $digit = $cardnumber [$i];
                if ($i % 2 == $parity)
                    $digit = $digit * 2;
                if ($digit > 9)
                    $digit = $digit - 9;
                $sum = $sum + $digit;
            }
            $result = ($sum % 10 == 0);
        } else {
            $result = false;
        }
        if (!$result) {
            $this->setMessage(empty($message) ? '@creditCard' : $value);
        }
        return $result;
    }

}