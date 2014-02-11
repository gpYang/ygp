<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Manual;

use System\Controller;

class StartController extends Controller {

    private $return = array(
        'controller' => 'start',
    );

    public function indexAction() {
        return $this->return;
    }

}

?>
