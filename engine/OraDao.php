<?php

/**
 * OraDao is query engine. It's responsable to manage connection with 
 * database.
 *
 * @author Federico Profeti (federico.profeti@gmail.com)
 */
class OraDao {
    
    private $connection;
    
    public static $username;
    public static $password;
    public static $tns;
    
    private $autocommit = true;

    /**
     * connect to database
     * @throws Exception
     */
    private function __construct() {
        $this->connection = oci_connect(self::$username, self::$password, self::$tns);
        if(!$this->connection){
            throw new Exception('Exception during database connection: '. oci_error());
        }
    }
    
    /**
     * Return an instance of OraDao
     * @return \static
     */
    public static function getInstance(){
        $static = null;
        if($static === NULL){
            $static = new static();
        }
        return $static;
    }
    
    /**
     * Return the current oracle connection
     * @return type
     */
    public function getConnection(){
        return $this->connection;
    }
    
    /**
     * Set the commit mode.
     * @param boolean $autoCommit
     */
    public function setAutoCommit($autoCommit){
        $this->autocommit = $autoCommit;
    }
    
    /**
     * Return the commit mode
     * @return boolean
     */
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
    
    /**
     * Load a persistent Object from database
     * @param PersistentObject $o model
     * @param boolean $forUpdate
     * @return array
     * @throws Exception on errors
     */
    /*public function load(PersistentObject $o, $forUpdate = false){
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
        $bindingArray = array();
        if(is_array($pk)){
            foreach ($pk as $val){
                eval('$colVal = $o->get'.$val.'();');
                $varName = ':'. $i++;
                echo "Binding $varName --> $colVal". PHP_EOL;
                $bindingArray[$varName] = $colVal;
                oci_bind_by_name($stid, $varName, $bindingArray[$varName]);
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
    }*/
    
    public function load(PersistentObject $o){
        $select = new SelectBuilder($o->getTableName());
        $where = new WhereCondition();
        $pk = $o->getPrimaryKey();
        if(empty($pk)){
            throw new Exception('Invalid primary key');
        }
        
        $select->addWhereCondition();
        $res = $this->executeSql($select);
        foreach ($res as $col => $val){
            eval("$o->set$col($val);");
        }
    }
    
    public function executeSql(SqlStatement $query, array $params = null){
        $q = Cache::getInstance()->getQuery($query);
        return $q->execute($params);
    }
}
