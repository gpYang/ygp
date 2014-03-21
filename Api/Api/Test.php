<?php

class Test {

    function a() {
//        dump(model('index/index', false));
        $model = model('index/a', true);
        $a = $model->from(array('p' => 'ppp'))->count('p_id');
//        return $this->return(true, 'aa');
        return array(true, $a);
    }

}

?>
