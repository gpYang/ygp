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
    private $_routeMatch = array(
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
     * @var array 路由规则
     */
    private $_ruleMatch = array();

    function __construct($config) {
        if (isset($config['route_rule']) && is_array($config)) {
            foreach ($config['route_rule'] as $value) {
                call_user_method_array('addRule', $this, $value);
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
        $this->_routeMatch = $this->parseRoute($route);
    }

    /**
     * 添加路由规则
     * @example addRule(array('index', 'user', 'user'), 'user/[:id]/data/[:name]', array('^\d+$', '^\w+$'))
     * @example addRule(array('index', 'user'), 'user')
     * hostname/user/123/data/kkk将匹配到index/user/user
     * hostname/user/kkk将匹配到/index/user/index
     * 
     * @param array $route
     * @param string $rule
     * @param array $grep
     */
    public function addRule($route, $rule, $grep = array()) {
        //preg_match_all('/(?<nogrep>\/*[^\[\/]*\/*)(?P<grep>\[:[^\]]*])/', $rule, $matches);
        $rule = explode('/', trim($rule, '/'));
        $i = 0;
        $routeMatch = &$this->_ruleMatch;
        foreach ($rule as $value) {
            $ruleMatch = 'nogrep';
            if (false !== strpos($value, ':')) {
                preg_match_all('/\[:([^\]]*)\]/', $value, $matches);
                $this->_routeGrepRule = array_merge($this->_routeGrepRule, (array) $matches[1]);
                $ruleMatch = 'grep';
                $value = $grep[$i];
                $i++;
            }
            !isset($routeMatch[$ruleMatch]) && $routeMatch[$ruleMatch] = array();
            !isset($routeMatch[$ruleMatch][$value]) && $routeMatch[$ruleMatch][$value] = array();
            $routeMatch = &$routeMatch[$ruleMatch][$value];
        }
        $routeMatch['index'] = $route;
    }

    /**
     * 解析路由
     * 
     * @param string $route uri
     * @return string
     */
    public function parseRoute($route) {
        $defaultRoute = array_values(config('route'));
        false !== strpos($route, '?') && $route = trim(substr($route, 0, strpos($route, '?')), '/');
        $breakRoute = $this->parseRouteByConfig($this->_ruleMatch, explode('/', $route));
        $routeMatch = array_keys($this->_routeMatch);
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
        foreach ($breakRoute as $value) {
            if (isset($config['nogrep'][$value])) {
                $config = &$config['nogrep'][$value];
                $route = $config['index'];
                continue;
            }
            if (isset($config['grep'])) {
                foreach ($config['grep'] as $grep => $v2) {
                    if (preg_match(sprintf('/%s/', $grep), $value, $matches)) {
                        $this->_routeGrepMatch = array_merge($this->_routeGrepMatch, $matches);
                        $config = &$config['grep'][$grep];
                        $route = $config['index'];
                        continue;
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
            if (strpos($this->_routeMatch['action'], '-')) {
                $exAction = explode('-', $this->_routeMatch['action']);
                $this->_routeMatch['action'] = $exAction[0];
                unset($exAction[0]);
                foreach ($exAction as $value) {
                    $this->_routeMatch['action'] .= ucfirst($value);
                }
            }
            if (!method_exists($class, $this->_routeMatch['action'] . 'Action')) {
                $this->notFound();
            }
        }
    }

    /**
     * 404
     */
    public function notFound() {
//        @ob_clean();
//        include PATH_APPLICATION . '/View/Error/404' . HTML_EXT;
        exit();
    }

    /**
     * 得到当前匹配路由
     * 
     * @return array
     */
    public function getRoute() {
        return $this->_routeMatch;
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

}

?>
