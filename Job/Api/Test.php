<?php

class Test {

    function a() {
        $model = model('index/a', true);
        $a = $model->from(array('aa' => 'a'))->count('id');
        return array(true, $a);
    }

}

?>
