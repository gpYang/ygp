<?php

namespace Index;

use System\Controller;

class TestController extends Controller {

    function aaVAction() {
        $this->model('index')->from('b')->insert(array(array('name' => '1'), array('name' => '2')));
    }

    function ss() {
        $this->model('index')->from('a')->select();
    }

}
