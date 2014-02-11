<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Manual;

use System\Controller;

class ConfigController extends Controller {

    private $return = array(
        'controller' => 'config',
    );

    public function indexAction() {
        return array_merge(array('action' => 'config-index'), $this->return);
    }

    public function globalAction() {
        return array_merge(array('action' => 'config-global'), $this->return);
    }

    public function eventsAction() {
        return array_merge(array('action' => 'config-events'), $this->return);
    }

    public function moduleAction() {
        return array_merge(array('action' => 'config-module'), $this->return);
    }

    public function othersAction() {
        return array_merge(array('action' => 'config-others'), $this->return);
    }

}

?>
