<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Manual;

use System\Controller;

class IndexController extends Controller {

    function indexAction() {
        return array();
    }

    function fileAction() {
        return array(
          'content' => file_get_contents($_GET['file']),
        );
        return false;
    }

}

?>
