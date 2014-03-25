<?php

/**
 * 简易分页类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library
 */

namespace Library;

/**
 * @package Library
 */
class Pager {

    /**
     * @var int 总条数
     */
    private $itemCount;

    /**
     * @var int 每页显示条数
     */
    private $perPageItem;

    /**
     * @var int 当前页
     */
    private $currentPage;

    /**
     * @var int 总页数
     */
    private $pageCount;

    /**
     * @var string 上下按钮的链接
     */
    private $url;

    /**
     * @var int 样式类型
     */
    private $styleId;

    /**
     * @var string url分页参数名
     */
    private $pageName = 'p';

    /**
     * @var array 样式数组
     */
    private static $style = array();

    /**
     * 构造
     * 
     * @param int $itemCount 总条数
     * @param int $perPageItem 每页条数
     * @param int $styleId 样式id
     */
    public function __construct($itemCount, $perPageItem, $styleId) {
        $this->perPageItem = $perPageItem > 0 ? $perPageItem : 20;
        $this->itemCount = $itemCount > 0 ? $itemCount : 0;
        $this->pageCount = (int) ceil($this->itemCount / $this->perPageItem);
        $this->pageCount > 0 || $this->pageCount = 1;
        $this->styleId = $styleId;
    }

    /**
     * 生成分页
     * 
     * @param int $currentPage 当前页码
     * @return string
     */
    public function show($currentPage = 1) {
        $pageSize = $this->pageName;
        $pageCount = $this->pageCount;
        $url = $this->url;
        if ($currentPage > $pageCount) {
            $currentPage = $pageCount;
        }
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        switch ($this->styleId) {
            case 1:
                self::setStyle(array(
                    'prev' => '',
                    'next' => '',
                    'goto' => 'btn btn-opt iptbtn',
                    'input' => 'in-t iptxt',
                    'click' => 'page',
                ));
                $output = '<div class="page"><span>共' . $this->itemCount . '条记录</span>' . "\n";
                if ($currentPage == 1) {
                    $output .= '<span class="no">上一页</span>' . "\n";
                } else {
                    $output .= '<a href="' . str_replace($pageSize, $currentPage - 1, $url) . '" id="page_prev" rel="' . ($currentPage - 1) . '" class="' . self::$style['prev'] . '">上一页</a>' . "\n";
                }
                if ($currentPage == $pageCount) {
                    $output .= '<span class="no">下一页</span>' . "\n";
                } else {
                    $output .= '<a href="' . str_replace($pageSize, $currentPage + 1, $url) . '" id="page_next" rel="' . ($currentPage + 1) . '" class="' . self::$style['next'] . '">下一页</a>' . "\n";
                }
                $output .= '<span>' . $currentPage . '/' . $pageCount . '</span>' . "\n";
                $output .= '<span class="ipt-combin">到<input id="to-page" type="text" class="' . self::$style['input'] . '" size="2" value="' . $currentPage . '" />页' . "\n";
                $output .= '<input type="button" class="' . self::$style['goto'] . '" value="" onclick="window.location.href=\'' . $url . '\'.replace(\'' . $pageSize . '\',document.getElementById(\'to-page\').value)"/></span>' . "\n";
                $output .= '</div>';
            default :
                break;
        }
        return $output;
    }

    /**
     * 设置样式
     * 
     * @param array $style
     * prev=>上一页按钮class
     * next=>下一页按钮class
     * goto=>跳转按钮class
     * input=>输入框class
     * click=>多分页数字按钮class
     * @return boolean
     */
    public static function setStyle($style) {
        if (!is_array($style)) {
            return false;
        }
        $pageStyle = array(
            'prev', 'next', 'goto', 'input', 'click'
        );
        foreach ($pageStyle as $value) {
            self::$style[$value] = isset($style[$value]) ? $style[$value] : '';
        }
        return true;
    }

    /**
     * 设置按钮链接
     * 
     * @param string $url
     */
    public function setUrl($url, $pageName = '_page_') {
        $this->url = $url;
        $this->pageName = $pageName;
    }

    /**
     * 获取连接
     * 
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

}

?>
