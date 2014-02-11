<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Manual;

use System\Controller;

class SystemController extends Controller {

    private $return = array(
        'controller' => 'system',
    );

    public function indexAction() {
        return array_merge(array('action' => 'system-index'), $this->return);
    }

    public function routerAction() {
        return array_merge(array('action' => 'system-global'), $this->return);
    }

    public function eventsAction() {
        return array_merge(array('action' => 'system-events'), $this->return);
    }

    public function moduleAction() {
        return array_merge(array('action' => 'system-module'), $this->return);
    }

    public function othersAction() {
        return array_merge(array('action' => 'system-others'), $this->return);
    }

}

?>
