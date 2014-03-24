<?php

class Test {

    function a() {
        return array(true, 1);
        $model = model('index/a', true);
        $a = $model->from(array('aa' => 'a'))->count('id');
        return array(true, $a);
    }

}

?>
