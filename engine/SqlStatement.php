<?php

/**
 * Common sql interface
 * @author Federico Profeti (federico.profeti@gmail.com)
 */
abstract class SqlStatement {
    
    protected $hash;
    private $statement;
    
    protected abstract function updateHash();
    
    /**
     * Destruct the select statement and leave resources
     */
    public function __destruct() {
        if(isset($this->statement)){
            oci_free_statement($this->statement);
        }
    }
    
    /**
     * Create the statement
     */
    private function createStatement(){
        if(!isset($this->statement)){
            $conn = OraDao::getInstance()->getConnection();
            $this->statement = oci_parse($conn, $this->getSqlQuery());
        }
    }
    
    /**
     * Get the sql query
     * @return string the sql string
     */
    public abstract function getSqlQuery();
    
    /**
     * Compare two select queres
     * @param QueryBuilder $q query compare
     * @return int comparsion result
     */
    public function compare(SqlStatement $q){
        return strcmp($this->hash, $q->hash);
    }
    
    /**
     * Executing the select statement with passed params
     * @param array $params query parameters [fieled1 => value, ... fieldN => paramN]
     * @return array[]
     */
    public function execute(array $params = null){
        $out = null;
        if(!empty($params)){
            foreach ($params as $k => $v){
                oci_bind_by_name($this->statement, ":$k", $v);
            }
        }
        if(!oci_execute($this->statement)){
            throw new Exception('Exception during query execute: '. oci_error());
        }
        if($this instanceof SelectBuilder){
            if(!oci_fetch_all($this->statement, $out)){
                throw new Exception('Exception during data fetching: '. oci_error());
            }
        }
        return $out;    
    }
    
    /**
     * Return the query hash
     * @return string
     */
    public function getHash(){
        return $this->hash;
    }
    
    public function __toString() {
        return $this->getSqlQuery();
    }
}
