<?php
require_once(dirname(__FILE__) . "/ValidatorFactory.php");

abstract class Model {
    private static $_validatorFactory = null;
    
    private $_values = array();
    private $_errors = array();
    private $_dirty = false;
    
    // Abstract "getSchema" function to return the schema
    protected abstract function getSchema();
    
    public function __construct() {
        $schema = $this->getSchema();
        $fields = $schema->getFields();
        
        foreach($schema->getFieldNames() as $field) {
            $this->_values[$field] = isset($fields[$field]["defaultValue"])
                ? $fields[$field]["defaultValue"]
                : null;
        }
    }
    
    public function getFieldNames() {
        return $this->getSchema()->getFields();   
    }
    
    public function get($field, $defaultValue=null) {
        return (isset($this->_values[$field]) || array_key_exists($field, $this->_values))
            ? $this->_values[$field]
            : $defaultValue;
    }
    
    public function set($field, $value) {
        if (!$this->has($field))
            throw new InvalidArgumentException("'{$field}' is not a recognized field");
        
        $this->_values[$field] = is_string($value) ? trim($value) : $value;
    }
    
    public function label($field) {
        $fields = $this->getSchema()->getFields();
        return $this->has($field) && isset($fields[$field]["label"])
            ? $fields[$field]["label"]
            : self::labelize($field);
    }
    
    public function has($field) {
        // All _values are instantiated during construction so it's synonymous with checking the Schema's fields
        return isset($this->_values[$field]) || array_key_exists($field, $this->_values);
    }
    
    public function addError($field, $message, $tokens=array()) {
        $schema = $this->getSchema();
        $label = $this->label($field);
            
        $tokens["field"] = $label;
        
        $message = preg_replace_callback('/\{\s*([a-z][a-z0-9_]*)\s*\}/i', function($m) use(&$tokens) {
            return isset($tokens[$m[1]]) ? $tokens[$m[1]] : $m[0];
        }, $message);
        
        if (!isset($this->_errors[$field]))
            $this->_errors[$field] = array($message);
        else
            $this->_errors[$field][] = $message;
            
        $this->_dirty = true;
        return $this;
    }
    
    public function hasErrors($field=null) {
        return $field === null
            ? $this->_dirty
            : isset($this->_errors[$field]);
    }
    
    public function clearErrors($field=null) {
        if ($field !== null) {
            unset($this->_errors[$field]);
            $this->_dirty = count($this->_errors) > 0;
        }
        else {
            $this->_errors = array();
            $this->_dirty = false;
        }   
        
        return $this;
    }
    
    public function getErrors($field=null) {
        return $field === null
            ? $this->_errors
            : (isset($this->_errors[$field]) ? $this->_errors[$field] : null);
    }
    
    public function getErrorSummary() {
        $errors=array();
        foreach($this->_errors as $field=>$errs) {
            if (is_array($errs) && count($errs) > 0)
                $errors[] = $errs[0];
        }
        return $errors;
    }
    
    public function validate() {
        $schema = $this->getSchema();

        foreach($schema->getRules() as $rule) {
            $validator = self::getValidatorFactory()->resolve($rule["validator"]);
            $args = isset($rule["args"]) ? $rule["args"] : null;

            if (is_string($rule["fields"]) || count($rule["fields"]) === 1) {
                $field = is_string($rule["fields"]) ? $rule["fields"] : $rule["fields"][0];
                $this->validateField($field, $rule, $validator, $args);
            }
            else {
                foreach($rule["fields"] as $field) {
                    $this->validateField($field, $rule, $validator, $args);
                }
            }
        }

        return !$this->hasErrors();
    }
    
    private function validateField($field, $rule, $validator, $args) {
        $value = $this->get($field);
        if (isset($rule["array"]) && $rule["array"] === true && is_array($value)) {
            foreach($value as $sub) 
                $validator->validate($this, $field, $sub, $args);
        }
        else {
            $validator->validate($this, $field, $value, $args);   
        }
    }
                    
    public static function getValidatorFactory() {
        return self::$_validatorFactory !== null
            ? self::$_validatorFactory
            : (self::$_validatorFactory = new ValidatorFactory());
    }
    
    public static function bind($object) {
        $class = get_called_class();
        $model = new $class();
        $schema = $model->getSchema();
        $isAssoc = is_array($object);
          
        foreach($schema->getFieldNames() as $field) {
            if ($isAssoc && isset($object[$field]))
                $model->set($field, $object[$field]);
            else if (!$isAssoc && isset($object->$field))
                $model->set($field, $object->$field);
        }
        
        return $model;
    }
    
    protected static function labelize($name) {
        $labelized = preg_replace_callback('/([A-Z]+|[0-9]+)/', function($x) { 
            return ' ' . (is_numeric($x[0][0]) ? $x[0].' ' : $x[0]);
        }, $name);

        return ucfirst($labelized);
    }
};