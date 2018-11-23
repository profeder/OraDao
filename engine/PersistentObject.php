<?php

include_once './OraDao.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PersistentObject
 *
 * @author fprofeti
 */

abstract class PersistentObject {
    
    private $pk;                                // Nome del campo che fa da primary key
    protected $obj;                             // Array contenente i campi restituiti
    private $columns;                           // Colonne specifiche su cui effettuare la select
    private $save = false;                      // Stato salvataggio dell'oggetto
    private $load = false;                      // Stato caricamento dell'oggetto
    
    /**
     * Restituisce il nome della tabella
     */
    public abstract function getTableName();
    
    protected function __construct(array $columns = null) {
        $this->columns = $columns;
    }
    
    /**
     * Imposta la chive primaria
     * @param string $name Chiave primaria
     */
    public function setPrimaryKey($pk){
        $this->pk = $pk;
    }
    /**
     * restituisce la chiave primaria
     * @return string
     */
    public function getPrimaryKey(){
        return $this->pk;
    }
    
    /**
     * restituisce le colonne
     * @return array | null
     */
    public function getColumns(){
        return $this->getColumns();
    }

    /**
     * 
     * @param type $name
     * @param type $value
     * @return type
     * @throws DaoException
     */
    public function __call($name, $value) {
        $type = substr($name, 0, 3);
        $columnName = substr($name, 3, strlen($name) - 3);
        if($type === 'get'){
            if(!$this->load){
                $obj = OraDao::getInstance()->load($this);
            }
            if(isset($this->obj[$columnName])){
                throw new DaoException("Required column $name not exist");
            }
            return $this->obj[$columnName];
        }elseif($type === 'set'){
            $this->obj[$columnName] = $value[0];
        }
    }
    
}
