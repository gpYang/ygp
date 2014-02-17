<?php

/**
 * 路由类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package System
 */

namespace System;

/**
 * @package System
 */
class Router {

    /**
     * @var array 路由
     */
    protected $routeMatch = array(
        'module' => '',
        'controller' => '',
        'action' => '',
    );

    /**
     * @var array 路由正则匹配成功的数据
     */
    private $_routeGrepMatch = array();

    /**
     * @var array 路由正则匹配规则 
     */
    private $_routeGrepRule = array();

    /**
     * @var array 路由正则匹配结果
     */
    private $_routeGrepResult = array();

    /**
     * @var array 路由规则
     */
    private $_ruleMatch = array();

    /**
     * 构造方法,添加路由规则
     * 
     * @param type $config
     */
    public function __construct($config) {
        if (isset($config['route_rule']) && is_array($config['route_rule'])) {
            foreach ($config['route_rule'] as $value) {
                call_user_func_array(array($this, 'addRule'), $value);
            }
        }
    }

    /**
     * 得到路由
     */
    public function route() {
        $route = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], '/') : trim($_SERVER['REQUEST_URI'], '/');
        $defaultRoute = array_values(config('route'));
        if (empty($route)) {
            $route = implode('/', $defaultRoute);
        }
        $this->_routeGrepRule = array();
        $this->_routeGrepMatch = array();
        $this->_routeGrepResult = array();
        return ($this->routeMatch = $this->parseRoute($route));
    }

    /**
     * 添加路由规则
     * @example addRule(array('index', 'user', 'user'), 'user/[:id]/data/[:name]', array('^\d+$', '^\w+$'))
     * @example addRule(array('index', 'user'), 'user')
     * hostname/user/123/data/kkk将匹配到index/user/user
     * hostname/user/kkk将匹配到/index/user/index
     * 
     * @param array $route 路由
     * @param string $rule 规则
     * @param string|array $grep 正则表达式
     */
    public function addRule($route, $rule, $grep = '') {
        //preg_match_all('/(?<nogrep>\/*[^\[\/]*\/*)(?P<grep>\[:[^\]]*])/', $rule, $matches);
        $rule = explode('/', trim($rule, '/'));
        $i = 0;
        $routeMatch = &$this->_ruleMatch;
        $routeGrepRule = array();
        foreach ($rule as $value) {
            $ruleMatch = 'nogrep';
            if (false !== strpos($value, ':')) {
                preg_match_all('/\[:([^\]]*)\]/', $value, $matches);
                if (!isset($matches[1])) {
                    continue;
                }
                $routeGrepRule = array_merge($routeGrepRule, $matches[1]);
                $ruleMatch = 'grep';
                $value = preg_quote(preg_replace('/\[:([^\]]*)\]/', '@', $value));
                if (is_array($grep)) {
                    for ($i; $i <= count($matches[1]); $i++) {
                        $value = preg_replace('/@/', sprintf('(%s)', rtrim(ltrim($grep[$i], '^'), '$')), $value, 1);
                    }
                } else
                    $value = str_replace('@', sprintf('(%s)', rtrim(ltrim($grep, '^'), '$')), $value);
            }
            !isset($routeMatch[$ruleMatch]) && $routeMatch[$ruleMatch] = array();
            !isset($routeMatch[$ruleMatch][$value]) && $routeMatch[$ruleMatch][$value] = array();
            $routeMatch = &$routeMatch[$ruleMatch][$value];
        }
        $routeMatch['match'] = array('index' => $route, 'grep' => $routeGrepRule);
    }

    /**
     * 解析路由
     * 
     * @param string $route uri
     * @return string
     */
    public function parseRoute($route) {
        $defaultRoute = array_values(config('route'));
        false !== strpos($route, '?') && $route = substr($route, 0, strpos($route, '?'));
        $breakRoute = $this->parseRouteByConfig($this->_ruleMatch, explode('/', $route));
        $routeMatch = array_keys($this->routeMatch);
        $result = array();
        for ($i = 0; $i <= 2; $i++) {
            $result[$routeMatch[$i]] = isset($breakRoute[$i]) ? $breakRoute[$i] : $defaultRoute[$i];
        }
        return $result;
    }

    /**
     * 根据配置解析路由
     * 
     * @param array $config 解析后的配置
     * @param array $breakRoute 分割后的路由
     * @return array
     */
    private function parseRouteByConfig($config, $breakRoute) {
        $route = $breakRoute;
        $count = count($breakRoute);
        $match = 0;
        $matches = $routeMatch = array();
        if (!empty($config)) {
            foreach ($breakRoute as $value) {
                if (isset($config['nogrep'][$value])) {
                    $config = &$config['nogrep'][$value];
                    isset($config['match']) && $this->_routeGrepRule = $config['match']['grep'];
                    $match += 1;
                    continue;
                }
                if (isset($config['grep'])) {
                    foreach ($config['grep'] as $grep => $ignore) {
                        if (preg_match(sprintf('/^%s$/', $grep), $value, $matches)) {
                            array_shift($matches);
                            $this->_routeGrepMatch = array_merge($this->_routeGrepMatch, $matches);
                            $config = &$config['grep'][$grep];
                            isset($config['match']) && $this->_routeGrepRule = $config['match']['grep'];
                            $match += 1;
                            break;
                        }
                    }
                }
            }
            if (isset($config['match']) && $match === $count) {
                !empty($this->_routeGrepMatch) && $this->_routeGrepResult = array_combine(array_slice($this->_routeGrepRule, 0, count($this->_routeGrepMatch)), $this->_routeGrepMatch);
                $route = $config['match']['index'];
                foreach ($route as $key => $value) {
                    if (false !== strpos($value, '?')) {
                        $trimMark = ltrim($value, '?');
                        isset($this->_routeGrepResult[$trimMark]) && $route[$key] = $this->_routeGrepResult[$trimMark];
                    }
                }
            }
        }
        return $route;
    }

    /**
     * 匹配路由
     * 
     * @param string $class 匹配到的控制器
     */
    public function match($class) {
        if (false !== $class) {
            if (strpos($this->routeMatch['action'], '-')) {
                $exAction = explode('-', $this->routeMatch['action']);
                $this->routeMatch['action'] = $exAction[0];
                unset($exAction[0]);
                foreach ($exAction as $value) {
                    $this->routeMatch['action'] .= ucfirst($value);
                }
            }
            if (!method_exists($class, $this->routeMatch['action'] . 'Action')) {
                $this->notFound();
            }
            $this->_routeGrepResult && $class->setGet($this->_routeGrepResult);
        }
    }

    /**
     * 404
     */
    public function notFound() {
        echo 333333333333333;
//        @ob_clean();
//        include PATH_APPLICATION . '/View/Error/404' . HTML_EXT;
        exit();
    }

    /**
     * 得到当前匹配路由
     * 
     * @param boolean $reMatch 是否重新匹配
     * @return array
     */
    public function getRouteMatch($reMatch = false) {
        if ($reMatch) {
            $this->route();
        }
        return $this->routeMatch;
    }

    /**
     * 判断是否为系统支持uri
     * 
     * @param string $route uri 
     * @return boolean
     */
    public function isRoute($route) {
        if (!preg_match('/^\/?[a-z]+\/[a-z]+\/[a-z]+\-*[a-z]*\/?$/', $route)) {
            return false;
        }
        return true;
    }
    
    /**
     * 获取路由正则匹配数据
     * 
     * @param string $name 数据名
     * @return array|string
     */
    public function getRouteGrepResult($name = '') {
        if ($name) {
            return $this->_routeGrepResult[$name];
        }
        return $this->_routeGrepResult;
    }

}

?>
