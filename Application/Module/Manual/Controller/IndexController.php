<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Manual;

use System\Controller;

class IndexController extends Controller {

    private $return = array(
        'controller' => 'index',
    );

    function indexAction() {
        return array_merge(array('action' => 'index-index'), $this->return);
        return $this->return;
    }

}

?>
