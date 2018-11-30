<?php

/**
 * Description of WhereCondition
 *
 * @author Federico Profeti (federico.profeti@gmail.com)
 */
class WhereCondition {
    
    /**
     * Bidimentional array use first level is used to AND condition 
     * and second level for OR condition
     * @var array
     */
    private $conditionChain;
    
    private $operation;
    private $field;
    private $label;
    
    public function addAndCondition(WhereCondition $c){
        if(!isset($this->conditionChain)){
            $this->conditionChain = array();
        }
        if(is_null($c->conditionChain)){
            $this->conditionChain[] = array($c);
        }else{
            $this->conditionChain[] = $c->conditionChain;
        }
    }
    
    public function addOrCondition(WhereCondition $c){
        if(!isset($this->conditionChain)){
            $this->conditionChain = array();
        }
        $len = count($this->conditionChain);
        if(is_null($c->conditionChain)){
            $this->conditionChain[$len][] = $c;
        }else{
            $this->conditionChain[$len][] = $c->conditionChain;
        }
    }
    
    public function printCondition($el = null, $and = true){
        if(is_null($el)){
            $el = $this;
        }
        if($el instanceof WhereCondition && is_null($el->conditionChain)){
            return $el->field.$el->operation.$el->label;
        }
        if(is_array($el) && !empty($el)){
            $tmp = array();
            foreach ($el as $e){
                $tmp[] = $this->printCondition($el, !$and);
            }
            if($and){
                $glue = ' AND ';
            }else{
                $glue = ' OR ';
            }
            $strOut = '('. implode($glue, $tmp). ')';
            return $strOut;
        }
        
    }

    public function __toString() {
        return "WHERE " . $this->printCondition();
    }
    
    private static $labelCounter = 0;

    private static function conditionFactory($operation, $field, $label = null, $table = null){
        $tmp = new WhereCondition();
        if(!empty($table)){
            $tmp->field = $table. '.';
        }
        $tmp->field .= $field;
        if(!is_null($label)){
            if(strlen($label) == 0){
                $this->label = '';
            }elseif(strlen($label)> 0 && $label[1] === ':'){
                $tmp->label = $label;
            }else{
                $tmp->label = ":$label";
            }
        }else{
            $tmp->label = ":$field".self::$labelCounter++;
        }
        $tmp->operation = $operation;
        return $tmp;
    }

    public static function eq($field, $label = null, $table = null){
        return self::conditionFactory('=', $field, $label, $table);        
    }
    
    public static function neq($field, $label = null, $table = null){
        return self::conditionFactory('<>', $field, $label, $table);        
    }
    
    public static function lt($field, $label = null, $table = null){
        return self::conditionFactory('<', $field, $label, $table);        
    }
    
    public static function lte($field, $label = null, $table = null){
        return self::conditionFactory('<=', $field, $label, $table);        
    }
    
    public static function gt($field, $label = null, $table = null){
        return self::conditionFactory('>', $field, $label, $table);        
    }
    
    public static function gte($field, $label = null, $table = null){
        return self::conditionFactory('>=', $field, $label, $table);        
    }
    
    public static function in($field, $label = null, $table = null){
        if(!empty($label)){
            $label = "(:$label)";
        }else{
            $label = '(:'.$field.self::$labelCounter++.')';
        }
        return self::conditionFactory('IN', $field, $label, $table);        
    }
    
    public static function notIn($field, $label = null, $table = null){
        if(!empty($label)){
            $label = "(:$label)";
        }else{
            $label = '(:'.$field.self::$labelCounter++.')';
        }
        return self::conditionFactory('NOT IN', $field, $label, $table);        
    }
    
    public static function isNull($field, $table = null){
        return self::conditionFactory('IS NULL', $field, '', $table);
    }
    
    public static function notIsNull($field, $table = null){
        return self::conditionFactory('NOT IS NULL', $field, '', $table);
    }
}
