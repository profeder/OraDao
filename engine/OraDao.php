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
    public static $tns;
    
    private $autocommit = true;

    private function __construct() {
        $this->connection = oci_connect(self::$username, self::$password, self::$tns);
        if(!$this->connection){
            throw new Exception('Exception during database connection: '. oci_error());
        }
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
        $name = preg_replace("[A-Z]", "_$0", $name);
        if($name[0] === '_'){
            $name = substr($name, 1);
        }
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
    
    public function load(PersistentObject $o, $forUpdate = false){
        $table = $o->getTableName();
        $select = $o->getColumns();
        if(empty($select)){
            $select = '*';
        }else{
            $select = array_map(function($val) use($table){
                $this->translateColumnName($val);
                return "$table.$val";
            }, $select);
            $select = implode(',', $select);
        }
        $pk = $o->getPrimaryKey();
        if(empty($pk)){
            throw new Exception('Invalid primary key');
        }
        $i = 0;
        if(is_array($pk)){
            $whereCond = array_map(function($val)use($table, $o, &$i){
                $varName =  strtolower($val);
                $this->translateColumnName($val);
                return "$table.$val = :".$i++;
            }, $pk);
            $whereCond = implode(' AND ', $whereCond);
        } else {
            $origName = $pk;
            $this->translateColumnName($pk);
            $whereCond = "$table.$pk=$origName";
        }
        $sql = "SELECT $select FROM $table WHERE $whereCond";
        if($forUpdate){
            $sql .= ' for update';
        }
        $colVal = null;
        echo $sql. PHP_EOL;
        $stid = oci_parse($this->connection, $sql);
        if(!$stid){
            throw new Exception('Exception during query parsing: '. oci_error());
        }
        $i = 0;
        if(is_array($pk)){
            foreach ($pk as $val){
                eval('$colVal = $o->get'.$val.'();');
                $varName = ':'. $i++;
                oci_bind_by_name($stid, $varName, $colVal);
                echo "Binding $varName --> $colVal". PHP_EOL;
            }
        } else {
            eval('$colVal = $o->get'.$val.'();');
            oci_bind_by_name($stid, ":$pk", $colVal);
        }
        $res = oci_execute($stid);
        if(!$res){
            throw new Exception('Exception during query execute: '. oci_error());
        }
        $out = array();
        $row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_NULLS);
        if(!$row){
            return null;
        }
        foreach ($row as $k => $v){
            $this->transtateObjectName($k);
            $out[$k] = $v;
        }
        unset($res);
        oci_free_statement($stid);
        return $out;
    }
    
}
