<?php

namespace Index;

use System\Model;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class IndexModel extends Model {

    public function __construct() {
        parent::__construct('writer', 'reader');
    }

    function get() {
        return $this->from('a')->select();
    }

}
