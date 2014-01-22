<?php

namespace Index;

use System\Controller;

class IndexController extends Controller {

    function indexAction() {
        return array(
            'hello' => 'hello',
            'name' => ' world'
        );
        return $this->logic()->test();
    }

}
