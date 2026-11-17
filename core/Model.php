<?php
class Model{
    private $db = NULL;

    // constructor
    public function  __construct($db=false){
        // store database class instance
        if($db)$this->db = $db;
    }

}// End Model class