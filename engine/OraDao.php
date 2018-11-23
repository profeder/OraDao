<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

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

    public function __construct() {
        $this->connection = oci_connect(self::$username, self::$password, self::$connStr);
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
        $name = substr(preg_replace("[A-Z]", "_$0", $name), 1);
        $name = strtoupper($name);
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
    
    private function load(PersistentObject $o){
        $table = $o->getTableName();
        $select = $o->getColumns();
        if(empty($select)){
            $select = '*';
        }else{
            $select = array_map(function($val) use($table){
                $traslated = $this->translateColumnName($val);
                return "$table.$traslated";
            }, $select);
            $select = implode(',', $select);
        }
        $sql = "SELECT $select FROM $table";
        echo $sql;
    }
    
    public function rollback(){
        $this->connection->rollback();
    }
    
    public function commit(){
        $this->connection->commit();
    }
}
