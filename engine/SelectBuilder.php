<?php

include_once 'SqlStatement.php';
/**
 * Create a query statement and SQL
 *
 * @author Federico Profeti (federico.profeti@gmail.com)
 */
class SelectBuilder extends SqlStatement{
    
    private $select;
    private $distinct = false;
    private $forUpdate = false;
    private $tables;
    private $where;
    private $order;
    private $groupBy;
    private $start;
    private $end;
    private $references;
    
    /**
     * Create new select
     * @param array $columns
     */
    public function __construct($table, array $columns = null) {
        if(!empty($columns)){
            $this->select = $columns;
        }
        if(is_array($table)){
            $this->tables = $table;
        }else{
            $this->tables = array($table);
        }
        $this->updateHash();
    }

    /**
     * Update query hash
     */
    protected function updateHash(){
        $this->hash = sha1(
                var_export($this->select, true) .
                var_export($this->distinct, true) .
                var_export($this->forUpdate, true) .
                var_export($this->tables, true) .
                var_export($this->references, true) .
                var_export($this->where, true) .
                var_export($this->order, true) .
                var_export($this->groupBy, true) .
                var_export($this->start, true) .
                var_export($this->end, true)
                );
    }

    /**
     * Add a table to statement
     * @param sting | array $table
     */
    public function addTable($table) {
        if(is_array($table)){
            $this->tables = array_merge($this->tables, $table);
        }else{
            $this->tables[] = $table;
        }
        $this->updateHash();
    }
    
    public function setWhereCondition(WhereCondition $w){
        $this->where = $w;
        $this->updateHash();
    }

        /**
     * Add reference key between 2 table
     * @param array $keys ['table1' => 'keyt1', 'table2' => 'keyt2', 'method' => eq]
     */
    public function addReferenceKeys(array $keys){
        if(empty($this->references)){
            $this->references = array();
        }
        $this->references[] = $keys;
        $this->updateHash();
    }
    
    /**
     * Generate and return the sql query
     * @return string Return the sql string
     */
    public function getSqlQuery(){
        $sql = 'SELECT ';
        if($this->distinct){
            $sql .= 'DISTINCT ';
        }
        if(empty($this->select)){
            $sql .= '* ';
        }else{
            foreach ($this->select as $k => $v){
                $sql .= "$k.$v,";
            }
            $sql[strlen($sql) - 1] = ' ';
        }
        if($this->forUpdate){
            $sql .= 'FOR UPDATE ';
        }
        $sql .= 'FROM '. implode(',', $this->tables);
        if(!empty($this->references) || !empty($this->where)){
            $sql.= ' '. $this->where;
        }
        if(!empty($this->groupBy)){
            $sql .= 'GROUP BY '. implode(',', $this->groupBy);
        }
        return $sql;
    }
    
}
