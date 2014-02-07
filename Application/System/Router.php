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
        return ($this->routeMatch = $this->parseRoute($route));
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
        $routeGrepRule = array();
        foreach ($rule as $value) {
            $ruleMatch = 'nogrep';
            if (false !== strpos($value, ':')) {
                preg_match_all('/\[:([^\]]*)\]/', $value, $matches);
                $routeGrepRule = array_merge($routeGrepRule, (array) $matches[1]);
                $ruleMatch = 'grep';
                $value = $grep[$i];
                $i++;
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
        false !== strpos($route, '?') && $route = trim(substr($route, 0, strpos($route, '?')), '/');
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
        foreach ($breakRoute as $value) {
            if (isset($config['nogrep'][$value])) {
                $config = &$config['nogrep'][$value];
                if (isset($config['match'])) {
                    $route = $config['match']['index'];
                    $this->_routeGrepRule = $config['match']['grep'];
                }
                continue;
            }
            if (isset($config['grep'])) {
                foreach ($config['grep'] as $grep => $v2) {
                    if (preg_match(sprintf('/%s/', $grep), $value, $matches)) {
                        $this->_routeGrepMatch = array_merge($this->_routeGrepMatch, $matches);
                        $config = &$config['grep'][$grep];
                        if (isset($config['match'])) {
                            $route = $config['match']['index'];
                            $this->_routeGrepRule = $config['match']['grep'];
                        }
                        break;
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
            $routeGerpRule = array_slice($this->_routeGrepRule, 0, count($this->_routeGrepMatch));
            $routeGerpRule && $class->setGet(array_combine($routeGerpRule, $this->_routeGrepMatch));
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
     * @param boolean $reMatch 是否重新匹配
     * @return array
     */
    public function getRouteMatch($reMatch) {
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

}

?>
