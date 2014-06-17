<?php
class Model{
    private $db = NULL;
    private $cache = NULL;    
     
    // constructor
    public function  __construct($db=false){
        // store database class instance
        if($db)$this->db = $db;
        // store Cache class instance
        $this->cache = Cache::getInstance();        
    }

}// End Model class