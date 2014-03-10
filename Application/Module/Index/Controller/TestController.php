<?php

namespace Index;

use System\Controller;

class TestController extends Controller {

    function aaAction() {
        $model = $this->model('index');
        $this->model('index')->from(array('b' => 'c'))->columns('`id`, `a`')->where('aa=1')->insert(array('a' => 'b', 'cc' => 'dd'), 'select from a');
        $model->from(array('ccc'=>'ddd'))
                ->join(array('aaa'=>'bbb'), 'id=ccc.id', 'name,key')
                ->columns('`id`, `a`')
                ->where(array('aa' => array('1', '2'), 'bb' => null))
                ->where('aa = 2', 'or', true)
                ->order(array('name asc', 'age DESC'))
                ->limit('1, 2')
                ->offset(2)
                ->delete();
        return false;
    }

}
