<?php

class Cache{
    
    private $cacheTable = array();
    private $refers = array();
    private $objects = 0;
    
    private $queryTable = array();
    
    public static function getInstance(){
        static $instance = null;
        if($instance === null){
            $instance = new static();
        }
        return $instance;
    }
    
    /**
     * Return the statement.
     * @param SqlStatement $q
     * @return SqlStatement
     */
    public function getQuery(SqlStatement $q){
        if(!isset($this->queryTable[$q->getHash()])){
            $this->queryTable[$q->getHash()]['statement'] = $q;
            $this->queryTable[$q->getHash()]['lastUse'] = new DateTime();
            $this->queryTable[$q->getHash()]['counter'] = 1;
        }else{
            $this->queryTable[$q->getHash()]['lastUse'] = new DateTime();
            $this->queryTable[$q->getHash()]['counter']++;
        }
        return $this->queryTable[$q->getHash()]['statement'];
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