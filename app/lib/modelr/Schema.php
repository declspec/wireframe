<?php
class Schema {
    private $_fieldNames;
    private $_fields;
    private $_rules;
    
    public function getFieldNames() { return $this->_fieldNames; }
    public function getFields()     { return $this->_fields; }
    public function getRules()      { return $this->_rules; }
    
    protected function __construct($fieldNames, $fields, $rules) {
        $this->_fieldNames = $fieldNames;
        $this->_fields = $fields;
        $this->_rules = $rules;
    }
    
    private static function labelize($str) {
        preg_replace_callback('/(?:[A-Z]+|[0-9]+)/', function($m) {
            return ' ' + is_numeric($m[0][0]) ? $m[0] + ' ' : $m[0];
        }, $str);
    }
    
    public static function compile($schema) {
        $fieldNames = array();
        $fields = array();
        
        foreach($schema["fields"] as $name=>$prop) {
            $fieldNames[] = $name;
            if(is_string($prop))
                $prop = array("label" => $prop);
            $fields[$name] = $prop;
        }
        
        return new Schema($fieldNames, $fields, $schema["rules"]);
    }    
};
?>