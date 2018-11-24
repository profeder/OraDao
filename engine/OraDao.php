<?php

/**
 * Description of OraDao
 *
 * @author fprofeti
 */
class OraDao {
    
    private $connection;
    
    public static $username;
    public static $password;
    public static $connStr;
    
    private $autocommit = true;

    private function __construct() {
        //$this->connection = oci_connect(self::$username, self::$password, self::$connStr);
    }
    
    public static function getInstance(){
        $static = null;
        if($static === NULL){
            $static = new static();
        }
        return $static;
    }
    
    public function setAutoCommit($autoCommit){
        $this->autocommit = $autoCommit;
    }
    
    public function getAutoCommit(){
        return $this->autocommit;
    }

    private function translateColumnName(&$name){
        echo $name. PHP_EOL;
        $name = preg_replace("[A-Z]", "_$0", $name);
        if($name[0] === '_'){
            $name = substr($name, 1);
        }
        $name = strtoupper($name);
        echo $name. PHP_EOL;
    }
    
    private function transtateObjectName(&$name){
         $name = strtolower($name);
        $len = strlen($name);
        $name[0] = strtoupper($name[0]);
        for($i = 1; $i < $len; $i++){
            if($name[$i] == '_'){
                $i++;
                $name[$i] = strtoupper($name[$i]);
            }
        }
        $name = str_replace('_', '', $name);
    }
    
    public function load(PersistentObject $o, $forUpdate = false){
        $table = $o->getTableName();
        $select = $o->getColumns();
        echo var_export($select, true).PHP_EOL;
        if(empty($select)){
            $select = '*';
        }else{
            $select = array_map(function($val) use($table){
                $this->translateColumnName($val);
                return "$table.$val";
            }, $select);
            $select = implode(',', $select);
        }
        $sql = "SELECT $select FROM $table";
        if($forUpdate){
            $sql .= ' for update';
        }
        echo $sql. PHP_EOL;
    }
    
    public function rollback(){
        $this->connection->rollback();
    }
    
    public function commit(){
        $this->connection->commit();
    }
}
