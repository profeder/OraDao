<?php

class Cache{
    
    private $cacheTable = array();
    private $refers = array();
    private $objects = 0;
    
    public static function getInstance(){
        static $instance = null;
        if($instance === null){
            $instance = new static();
        }
        return $instance;
    }
    
    public function add(PersistentObject $o){
        $table = $o->getTableName();
        $pkVal = $o->getPrimaryKeyValue();
        if(empty($pkVal)){
            return;
        }
        if(!isset($this->cacheTable[$table])){
            $this->cacheTable[$table] = array();
        }
        $this->cacheTable[$table][$pkVal] = $o;
        $this->refers[$table.$pkVal] = 1;
        $this->objects++;
    }
    
    public function find($table, $k){
        if(!isset($this->cacheTable[$table])){
            return null;
        }
        $row = $this->cacheTable[$table];
        if(isset($row[$k])){
            $this->refers[$table.$k]++;
            return $row[$k];
        }
        return null;
    }
}