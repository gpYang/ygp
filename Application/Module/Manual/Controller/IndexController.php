<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Scan;

use System\Controller;

class IndexController extends Controller {

    function dirAction() {
        $top = !empty($_GET['top']) ? $_GET['top'] : PATH_APPLICATION;
        return array(
            'top' => $top,
            'scan' => scandir($top)
        );
    }

    function fileAction() {
        return array(
          'content' => file_get_contents($_GET['file']),
        );
        return false;
    }

}

?>
