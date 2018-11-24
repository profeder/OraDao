<?php

include_once 'OraDao.php';

/**
 * Description of PersistentObject
 *
 * @author fprofeti
 */

abstract class PersistentObject {
    
    protected $pk;                              // Nome del campo che fa da primary key
    protected $obj;                             // Array contenente i campi restituiti
    private $columns;                           // Colonne specifiche su cui effettuare la select
    private $save = false;                      // Stato salvataggio dell'oggetto
    private $load = false;                      // Stato caricamento dell'oggetto
    
    /**
     * Restituisce il nome della tabella
     */
    public abstract function getTableName();
    
    public function __construct(array $columns = null) {
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
     * Restituisce i valori delle chiavi primarie
     * @return string
     */
    public function getPrimaryKeyValue(){
        if(!isset($this->pk)){
            return;
        }
        $strOut = '';
        if(is_array($this->pk)){
            foreach ($this->pk as $el){
                $strOut .= $this->obj[$el];
            }
        }else{
            $strOut .= $this->obj[$this->pk];
        }
        return $strOut;
    }
    
    /**
     * restituisce le colonne
     * @return array | null
     */
    public function getColumns(){
        return $this->columns;
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
            if(!$this->load && !in_array($columnName, $this->pk)){
                $obj = OraDao::getInstance()->load($this);
                if(empty($obj)){
                    throw new Exception ('Error on fetching data');
                }
                $this->columns = array_keys($obj);
                $this->obj = $obj;
                $this->load = true;
            }
            if(!isset($this->obj[$columnName])){
                throw new Exception("Required column $columnName not exist");
            }
            return $this->obj[$columnName];
        }elseif($type === 'set'){
            $this->obj[$columnName] = $value[0];
        }
    }
    
}
