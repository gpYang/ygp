<?php

/**
 * 身份证处理类
 * 
 * @author unkown
 * @package Library.Valid
 */

namespace Library\Valid;

/**
 * @package Library.Valid
 */
class IDCard {

    //检证身份证是否正确
    public static function isCard($card) {
        $card = self::to18Card($card);
        if (strlen($card) != 18 || !preg_match('/^(\d|X)+$/', $card)) {
            return false;
        }

        $cardBase = substr($card, 0, 17);

        return (self::getVerifyNum($cardBase) == strtoupper(substr($card, 17, 1)));
    }

    //取出生日期
    public static function getBirth($card) {
        $card = self::to18Card($card);

        if (!self::isCard($card))
            return false;
        return substr($card, 6, 4) . '-' . substr($card, 10, 2) . '-' . substr($card, 12, 2);
    }

    //取年龄
    public static function getAge($card) {
        $card = self::to18Card($card);

        if (!self::isCard($card))
            return false;
        $y = substr($card, 6, 4);
        $m = substr($card, 10, 2);
        $d = substr($card, 12, 2);

        $today = explode('-', date('Y-m-d'));
        if ($today[0] < $y)
            return false;
        //足年的
        if ($today[0] > $y) {
            return $today[0] - $y; // . '岁';
        }
        //足月的
        if ($today[1] > $m) {
            return $today[1] - $m . '个月';
        }
        //足日的
        if ($today[2] > $d) {
            return $today[2] - $d . '天';
        }

        return false;
    }

    //15位的旧身份证,最后一个数是单数的为男，双数的为女
    //18位的新身份证,倒数第二位是单数的为男，双数的为女
    //返回值：1 男，2 女
    public static function getSex($card) {
        $card = self::to18Card($card);

        if (!self::isCard($card))
            return false;

        $card = substr($card, -2, 1) % 2;

        return $card == 1 ? 1 : 2;
    }

    //格式化15位身份证号码为18位
    public static function to18Card($card) {
        $card = trim($card);

        if (strlen($card) == 18) {
            return $card;
        }

        if (strlen($card) != 15) {
            return false;
        }

        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
        if (array_search(substr($card, 12, 3), array('996', '997', '998', '999')) !== false) {
            $card = substr($card, 0, 6) . '18' . substr($card, 6, 9);
        } else {
            $card = substr($card, 0, 6) . '19' . substr($card, 6, 9);
        }
        $card = $card . self::getVerifyNum($card);
        return $card;
    }

    // 计算身份证校验码，根据国家标准gb 11643-1999
    private static function getVerifyNum($cardBase) {
        if (strlen($cardBase) != 17) {
            return false;
        }
        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);

        // 校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

        $checksum = 0;
        for ($i = 0; $i < strlen($cardBase); $i++) {
            $checksum += substr($cardBase, $i, 1) * $factor[$i];
        }

        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];

        return $verify_number;
    }

}

?>
