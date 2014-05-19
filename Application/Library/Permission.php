<?php

/**
 * 权限类
 * 
 * @author yangguipeng<hi121073215@gmail.com>
 * @package Library
 */

namespace Library;

/**
 * @package Library
 */
class Permission {
    
    /**
     * @staticvar string 条件
     */
    const IN = 'in';
    const EQUALTO = 'eq';
    const LESSTHAN = 'lt';
    const GREATTHAN = 'gt';
    const BETWEEN = 'bt';
    const BETWEEN_EQUAL = 'be';

    /**
     * @staticvar string 匹配任意条件字符
     */
    const ALL = '*';

    /**
     * 权限规则 
     * 
     * @var array 
     */
    private $_rule = array();

    /**
     * 是否开启验证
     * 
     * @var boolean
     */
    private $_isAuth = false;

    /**
     * 是否能直接对所有模块配置
     * 
     * @var boolean
     */
    private $_canSetAll = true;

    /**
     * 禁止进入方法
     * 
     * @var object|null
     */
    private $_denyFuncion;

    /**
     * 路由总层级
     * 
     * @var int 
     */
    private $_routeDeep = 3;

    /**
     * 构造,设置路由层级和权限规则
     * 
     * @param array $rule 路由规则
     * @param int $routeDeep 路由层级
     */
    public function __construct($rule = array(), $routeDeep = 3) {
        $this->setRouteDeep($routeDeep);
        foreach ($rule as $value) {
            call_user_func_array(array($this, 'addRule'), $value);
        }
    }

    /**
     * 添加路由规则
     * 
     * @param array $route 路由
     * @param string $type 验证类型
     * @param array $condition 验证条件
     */
    public function addRule($route, $type, $condition = array()) {
        if ($route === self::ALL)
            $route = array_fill(0, $this->_routeDeep, $route);
        else if (($count = count($route)) !== $this->_routeDeep)
            $route = array_slice($route, $count - $this->_routeDeep);
        $this->_rule[implode('/', $route)][$type] = $condition;
    }

    /**
     * 设置路由层级
     * 
     * @param int $deep 层级数
     */
    public function setRouteDeep($deep) {
        $deep = intval($deep);
        if ($deep >= 1) {
            $this->_routeDeep = $deep;
        }
    }

    /**
     * 开启/关闭验证
     * 
     * @param boolean $isAuth 是否验证
     */
    public function setAuth($isAuth) {
        $this->_isAuth = $isAuth;
    }

    /**
     * 开启/关闭验证
     * 
     * @param boolean $isAuth 是否验证
     */
    public function getAuth($isAuth) {
        $this->_isAuth = $isAuth;
    }

    /**
     * 获取验证规则
     * 
     * @return array
     */
    public function getRule() {
        return $this->_rule;
    }

    /**
     * 禁止进入方法
     * 
     * @param object $function
     */
    public function setDenyFunction($function) {
        $this->_denyFuncion = $function;
    }

    /**
     * 校验
     * 
     * @param array $route 路由
     * @param string $type 校验类型
     * @param mixed $value 校验值
     * @return boolean
     */
    public function check($route, $type, $value) {
        if (!$this->_isAuth || $this->checkAllow(implode('/', $route), $type, $value))
            return true;
        if ($this->_canSetAll) {
            $changeTo = array_fill(0, $this->_routeDeep, self::ALL);
            if ($this->checkAllow(implode('/', $changeTo), $type, $value)) {
                return true;
            }
            for ($deep = 0; $deep < $this->_routeDeep; $deep++) {
                $cloneRoute = $route;
                $cloneChangeTo = $changeTo;
                $cloneRoute[$deep] = $changeTo[$deep];
                $cloneChangeTo[$deep] = $route[$deep];
                if ($this->checkAllow(implode('/', $cloneRoute), $type, $value) || $this->checkAllow(implode('/', $cloneChangeTo), $type, $value)) {
                    return true;
                }
            }
        }
        if (is_object($this->_denyFuncion)) {
            call_user_func($this->_denyFuncion);
        }
        return false;
    }

    /**
     * 验证是否允许进入
     * 
     * @param string $implodeRoute 路由组成
     * @param string $type 类型
     * @param mixed $value 校验值
     * @return boolean
     */
    private function checkAllow($implodeRoute, $type, $value) {
        if (isset($this->_rule[$implodeRoute][self::ALL])) {
            return true;
        }
        if (isset($this->_rule[$implodeRoute][$type])) {
            if ($this->checkCondition($this->_rule[$implodeRoute][$type], $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 校验条件
     * 
     * @param array $condition 条件
     * @param mixed $value 校验值
     * @return boolean
     */
    private function checkCondition($condition, $value) {
        foreach ($condition as $conditionName => $conditionValue) {
            switch ($conditionName) {
                case self::EQUALTO :
                    if (!($conditionValue === $value)) {
                        return false;
                    }
                    break;
                case self::IN :
                    if (!(in_array($value, $conditionValue)))
                        return false;
                    break;
                case self::GREATTHAN :
                    if (!($value > $conditionValue))
                        return false;
                    break;
                case self::LESSTHAN :
                    if (!($value < $conditionValue))
                        return false;
                    break;
                case self::BETWEEN :
                    if (!($value > $conditionValue[0] && $value < $conditionValue[1]))
                        return false;
                    break;
                case self::BETWEEN_EQUAL :
                    if (!($value >= $conditionValue[0] && $value <= $conditionValue[1]))
                        return false;
                    break;
                default :
                    return false;
            }
        }
        return true;
    }

}

?>
