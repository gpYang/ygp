<?php

use System\Model;

class AMODEL extends Model {

    public function __construct() {
        parent::__construct('writer', 'reader');
    }

}

?>
