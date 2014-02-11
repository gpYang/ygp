<?php

/**
 * Curl 类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library
 */

namespace Library;

/**
 * @package Library
 */
class Curl {

    private $_ch = null;
    private $_timeout = 10;
    private $_method = '';
    private $_h = '';
    private $_header_req = '';
    private $_header_res = '';
    private $_body = '';
    private $_crlf = "";
    private $_url_ori = '';
    private $_url = array();
    private $_error = array();
    private $_cookie = array();
    private $_options_base = array();
    private $_options_init = array();
    private $_options_tmp = array();

    public function __destruct() {
        $this->close();
    }

    public function __construct($options = array(), $format = false) {
        $options_base = array();
        if ($format == false) {
            $options_base = array(
                "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:23.0) Gecko/20100101 Firefox/23.0",
                "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                "Accept-Language: zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3",
                "Accept-Encoding: gzip, deflate",
//                "Connection: Keep-Alive",
                "Connection: close",
            );
        }
        $this->_options_base = array_merge($options_base, (array) $options);
    }

    public function init($options = array(), $format = false) {
        $options = (array) $options;
        $this->_options_init = $format == false ? array_merge((array) $this->_options_base, $options) : $options;
        $this->_ch = curl_init();
        //是否将头文件的信息作为数据流输出(HEADER信息),这里保留报文
        curl_setopt($this->_ch, CURLOPT_HEADER, true);
        //获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
        //设置连接等待时间,0不等待
        curl_setopt($this->_ch, CURLOPT_CONNECTTIMEOUT, $this->_timeout);
        //设置curl允许执行的最长秒数
        curl_setopt($this->_ch, CURLOPT_TIMEOUT, $this->_timeout);
        //默认使用IPv4解析域名
        curl_setopt($this->_ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        return $this;
    }

    public function set_options($options = array(), $format = false) {
        $options = (array) $options;
        $this->_options_tmp = $format == false ? array_merge((array) $this->_options_init, $options) : $options;
        return $this;
    }

    public function set_cookie($cookie = array(), $format = false) {
        $cookie = (array) $cookie;
        $this->_cookie = $format == false ? array_merge((array) $this->_cookie, $cookie) : $cookie;
        return $this;
    }

    private function gett($url, $query = array(), $followlocation = true) {
        $this->set_cookie();
        if (!empty($query)) {
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= is_array($query) ? http_build_query($query) : $query;
        }
        $this->_url_ori = $url;
        $this->_url = $this->get_url_analysis($url);
        $this->_method = 'GET';
        $this->_body = '';
        $this->_requrest($followlocation);
        return $this;
    }

    public function get($url, $query = array(), $followlocation = true) {
        $this->_header_res = '';
        return call_user_func_array(array($this, 'gett'), func_get_args());
    }

    public function post($url, $query = array(), $followlocation = true) {
        $this->set_cookie();
        $this->_url_ori = $url;
        $this->_url = $this->get_url_analysis($url);
        $this->_method = 'POST';
        $this->_header_res = '';
        $this->_body = '';
        if (!empty($query)) {
            $query = is_array($query) ? http_build_query($query) : $query;
            //POST参数，如果要传送一个文件，需要一个@开头的文件名
            curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $query);
        }
        $this->_requrest($followlocation);
        return $this;
    }

    public function close() {
        //do sth...
        return $this;
    }

    public function ssl() {
        // do sth...
        return $this;
    }

    public function http_code() {
        //do sth...
        return '';
    }

    public function effective_url() {
        //do sth...
        return '';
    }

    public function get_header() {
        return $this->get_header_res();
    }

    public function get_header_req() {
        return $this->_header_req;
    }

    public function get_header_res() {
        return $this->_header_res;
    }

    public function get_body() {
        return $this->_body;
    }

    public function get_cookie() {
        return $this->_cookie;
    }

    private function get_options() {
        $options = $this->_options_tmp;
        call_user_func_array('array_unshift', array(
            &$options,
            //$this->_method . ' ' . $this->_url['path'] . ($this->_url['query'] ? '?' : '') . $this->_url['query'] . ' HTTP/1.1',
            $this->_method . ' ' . $this->_url_ori . ' HTTP/1.1',
            'Host: ' . $this->_url['host'],
        ));
        $cookie_tmp = array();
        while (list($k, $v) = each($this->_cookie)) {
            $cookie_tmp[] = $k . "=" . urldecode($v);
        }
        if (!empty($cookie_tmp)) {
            $options[] = 'Cookie: ' . implode(';', $cookie_tmp);
        }
        return $options;
    }

    /**
     * 获取头部如Location,Set-Cookie等数据
     * @param type $key
     * @return type
     */
    public function get_response_value_by_header($key) {
        preg_match_all("/{$key}\:\s*(.+?)\s*\n/i", $this->_h, $m);
        return count($m[1]) == 1 ? $m[1][0] : $m[1];
    }

    /**
     * 解析获取Set-Cookie回来的数据
     * @param type $cookie
     * @return boolean
     */
    public function get_cookie_analysis($cookie) {
        $_cr = array();
        $_ce = explode(';', $cookie);
        $i = 0;
        foreach ((array) $_ce as $v1) {
            $_cee = explode('=', $v1);
            if ($i == 0) {
                $_cr['name'] = $_cee[0];
                $_cr['value'] = $_cee[1];
            } else {
                switch (trim($_cee[0])) {
                    case 'expires': $_cr['expires'] = $_cee[1];
                        break;
                    case 'path': $_cr['path'] = $_cee[1];
                        break;
                    case 'domain': $_cr['domain'] = $_cee[1];
                        break;
                    case 'httponly': $_cr['httponly'] = true;
                        break;
                }
            }
            $i++;
        }
        return $_cr;
    }

    public function get_url_analysis($url) {
        $url = (array) parse_url($url);
        if (empty($url['port'])) {
            $url['port'] = 80;
        }
        if (empty($url['query'])) {
            $url['query'] = "";
        }
        return $url;
    }

    private function _requrest($followlocation = true) {
        if ($followlocation == false) {
            curl_setopt($this->_ch, CURLOPT_FOLLOWLOCATION, false);
        }
        if (empty($this->_options_tmp)) {
            $this->set_options();
        }
        $url = $this->_url['scheme'] . '://' . $this->_url['host'] . ':' . $this->_url['port'];
        $url .= '/' . ltrim((isset($this->_url['path']) ? $this->_url['path'] : ''), '/');
        $url .= ($this->_url['query'] ? '?' : '') . $this->_url['query'];
        //需要获取的URL地址
        curl_setopt($this->_ch, CURLOPT_URL, $url);
        //请求类型
        curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $this->_method);
        //设置发送出的HTTP请求头信息
        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, ($this->_header_req = $this->get_options()));
        $response = curl_exec($this->_ch);
        $errno = curl_errno($this->_ch);
        if ($errno > 0) {
            $this->error('Errno::' . $errno . '::' . curl_error($this->_ch));
        }
        //获取header部分的大小
        $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
        $this->_h = substr($response, 0, $header_size);
        $this->_header_res .= $this->_h;
        $this->_body = substr($response, $header_size);
        $originalCookie = $this->get_response_value_by_header('Set-Cookie');
        foreach ((array) $originalCookie as $value) {
            $_cc = $this->get_cookie_analysis($value);
            $this->_cookie[$_cc['name']] = $_cc['value'];
        }
        if ($followlocation == true) {
            $this->_followlocation();
        }
        $this->_options_tmp = array();
    }

    private function _followlocation() {
        $location = $this->get_response_value_by_header('Location');
        if (!empty($location)) {
            if (is_array($location)) {
                $location = $location[count($location) - 1];
            }
            $this->gett($location);
        }
    }

    public function error($string = '', $keep = true) {
        if (empty($string)) {
            foreach ((array) $this->_error as $value) {
                $this->error($value, false);
            }
        } else {
            if ($keep) {
                $this->_error[] = (string) $string;
            }
            echo $string, '<br>';
        }
    }

}