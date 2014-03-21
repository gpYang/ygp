<?php

/**
 * 文件上传类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library
 */

namespace Library;

/**
 * @package Library
 */
class Upload {

    /**
     * @var int 可上传的最小文件大小
     */
    private $_minSize = 0;

    /**
     * @var int || null 可上传的最大文件大小
     */
    private $_maxSize = '';

    /**
     * @var string 上传文件根目录
     */
    private static $_rootDir = '';

    /**
     * @var string 上传的目录
     */
    private $_dir = '';

    /**
     * @var string 文件后缀
     */
    private $_fileExt = '';

    /**
     * @var string 上传后的文件名
     */
    private $_filename = '';

    /**
     * @var array 获取到的$_file数组
     */
    private $_reachFile = array();

    /**
     * 'eventType' => 'upload' 事件类型
     * 'description' => '' 描述
     * 'ip' => '' 上传的ip
     * 'preFileName' => '', //上传前的文件名
     * 'newFileName' => '', //上传后的文件名
     * 'fileExt' => '', //文件后缀
     * 'fileSize' => '', //文件大小
     * 'fileType' => '', //文件类型
     * 'fileDir' => '', //上传的路径
     *
     * @var array
     */
    private $_log = array();

    /**
     * @var array 文件类型的数组,从配置文件中获取,用于辨认伪后缀文件
     */
    private $_fileType = array();

    /**
     * @var array 允许上传文件类型
     */
    private $_canUploadType = array();

    /**
     * @var boolen 确认接收到的文件是否可以上传
     */
    private $_canUpload = false;

    /**
     * @var boolen 日志开关
     */
    private $_logOpen = true;

    /**
     * @var boolean 是否使用随机名称
     */
    private $_randName = true;

    /**
     * 设置日志目录
     * 
     * @param string $path 路径
     */
    public static function setUploadPath($path) {
        self::$_rootDir = $path;
    }

    /**
     * 构造函数
     * 
     * @param string $fileInput 控件名
     * @param string $fileSize 上传大小限制
     * @param array $canUploadType 上传后缀限制
     */
    public function __construct($fileInput, $fileSize, $canUploadType) {
        $this->setFileSize($fileSize);
        $this->_reachFile = $this->getFile($fileInput);
        $this->_fileType = $this->getFileType();
        $this->_canUploadType = $canUploadType;
        $this->_canUpload = $this->checkFile();
        if ($this->_canUpload) {
            if (!($this->_dir = $this->getDir())) {
                return '创建上传目录失败';
            }
            $this->_filename = $this->getName();
        }
    }

    /**
     * 析构函数(当对象销毁时进行日志操作)
     */
    public function __destruct() {
        if ($this->_logOpen && !empty($this->_log)) {
            $ip = get_client_ip();
            $eventType = 'upload';
            foreach ((array) $this->_log as $log) {
                $log['ip'] = $ip;
                $log['eventType'] = $eventType;
                $description = $log['description'];
                unset($log['description']);
                $this->logToFile($description, 'warn', $log);
            }
        }
    }

    /**
     * 魔术方法,可在外部设置log开关
     *
     * @param string 变量名
     * @param string 变量值
     * @return boolen || string
     */
    public function __set($name, $value) {
        if ($name != '_logOpen' && $name != '_canUploadType' && $name != '_randName') {
            return false;
        }
        return $this->{$name} = $value;
    }

    /**
     * 设置接收到的数组
     *
     * @param string $fileInput 控件名
     * @return array
     */
    private function getFile($fileInput) {
        return $_FILES[$fileInput];
    }

    /**
     * 设置文件大小限制
     *
     * @param string $fileSize 文件限制(K)
     */
    private function setFileSize($fileSize) {
        if (empty($fileSize)) {
            return true;
        }
        if (false === strpos($fileSize, ':')) {
            $this->_minSize = $fileSize * 1000;
        } else {
            $fileSizes = explode(':', $fileSize);
            !empty($fileSizes[0]) && $this->_minSize = $fileSizes[0] * 1000;
            !empty($fileSizes[1]) && $this->_maxSize = $fileSizes[1] * 1000;
        }
    }

    /**
     * 获取随机文件名
     *
     * @return string
     */
    private function getName() {
        if ($this->_randName) {
            return sha1(uniqid(get_rand())) . $this->_fileExt;
        }
        return $this->_reachFile['name'];
    }

    /**
     * 获取文件类型数组
     *
     * @return array
     */
    private function getFileType() {
        return \System\Config::getConfigFromFile('filetype');
    }

    /**
     * 检查文件是否可上传
     *
     * @return boolen
     */
    private function checkFile() {
        $this->_fileExt = strrchr($this->_reachFile['name'], '.');
        if ($this->_reachFile['error']) {
            return false;
        }
        if (false === strpos($this->_reachFile['name'], '.')) {
            $this->log(1);
            return false;
        }
        if (!empty($this->_canUploadType)) {
            if (!in_array($this->_fileExt, $this->_canUploadType)) {
                $this->log(2);
                return false;
            }
        }
        if (!empty($this->_maxSize)) {
            if ($this->_reachFile['size'] > $this->_maxSize || $this->_reachFile['size'] < $this->_minSize) {
                $this->log(3);
                return false;
            }
        } else {
            if ($this->_reachFile['size'] < $this->_minSize) {
                $this->log(3);
                return false;
            }
        }
        $contentType = $this->_fileType[ltrim($this->_fileExt, '.')];
        if (is_array($contentType)) {
            if (!in_array($this->_reachFile['type'], $contentType)) {
                $this->log(4);
                return false;
            }
        } else {
            if ($this->_reachFile['type'] != $contentType) {
                $this->log(4);
                return false;
            }
        }
        return true;
    }

    /**
     * 递归设置文件上传路径
     *
     * @return string
     */
    private function getDir() {
        $this->_dir = self::$_rootDir . '/';
        $_dir = $this->_dir . str_replace('-', '/', date('Y-m-d', time()));
        if (!is_dir($_dir)) {
            if (!mkdir($_dir, 0755, true)) {
                return false;
            }
        }
        return $_dir;
    }

    /**
     * 文件上传
     *
     * @param string $copy_to_path 如果指定该目录，上传的时候之后，复制一份文件到指定目录
     * @return boolen || string
     */
    public function upload() {
        if (false === $this->_canUpload) {
            $return = end($this->_log);
            return $return['description'];
        }
        if (move_uploaded_file($this->_reachFile['tmp_name'], $this->_dir . '/' . $this->_filename)) {
            $this->log(0);
            return end($this->_log);
        }
    }

    /**
     * 记录日志
     *
     * @param int $type 0:上传成功,1:无后缀,2:不允许的后缀,3:超过大小限制,4:伪后缀
     */
    private function log($type) {
        $log = array(
            'preFileName' => $this->_reachFile['name'],
            'newFileName' => $this->_filename,
            'fileExt' => $this->_fileExt,
            'fileSize' => $this->_reachFile['size'],
            'fileType' => $this->_reachFile['type'],
            'fileDir' => $this->_dir,
        );
        switch ($type) {
            case 0:
                $log['description'] = '上传文件成功';
                break;
            case 1:
                $log['description'] = '试图上传文件失败, 无后缀名';
                break;
            case 2:
                $log['description'] = '试图上传文件失败, 不允许的后缀名';
                break;
            case 3:
                $log['description'] = '试图上传文件失败, 超过文件大小限制';
                break;
            case 4:
                $log['description'] = '试图上传文件失败, 后缀名与文件类型不匹配';
                break;
            default:
                break;
        }
        $this->_log[] = $log;
        return end($this->_log);
    }
    
    /**
     * 记录到文件,便于迁移
     * 
     * @param type $description
     * @param type $logLevel
     * @param type $logs
     */
    private function logToFile($description, $logLevel, $logs) {
        logs($description, $logLevel, $logs);
    }

    /**
     * 图像处理
     */
    public function dealImg() {
        
    }

}

?>
