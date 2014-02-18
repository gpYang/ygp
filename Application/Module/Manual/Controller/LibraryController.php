<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Manual;

use System\Controller;

class LibraryController extends Controller {

    private $return = array(
        'controller' => 'library',
    );

    public function indexAction() {
//        return $this->view(array_merge(array('action' => 'system-index'), $this->return), 'manual/system/router');
        return array_merge(array('action' => 'library-index'), $this->return);
    }

    public function dbAction() {
        return array_merge(array('action' => 'library-db'), $this->return);
    }

    public function eventAction() {
        return array_merge(array('action' => 'system-event'), $this->return);
    }

    public function controllerAction() {
        return array_merge(array('action' => 'system-controller'), $this->return);
    }

    public function logicAction() {
        return array_merge(array('action' => 'system-logic'), $this->return);
    }

    public function modelAction() {
        return array_merge(array('action' => 'system-model'), $this->return);
    }

    public function viewAction() {
        return array_merge(array('action' => 'system-view'), $this->return);
    }

}

?>
