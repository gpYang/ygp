<?php

class Test {

    function a() {
//        dump(model('index/index', false));
        $model = model('index/a', true);
        $a = $model->from(array('p' => 'properties_property'))->count('property_id');
//        return $this->return(true, 'aa');
        return array(true, $a);
    }

}

?>
