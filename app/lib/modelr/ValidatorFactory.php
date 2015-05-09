<?php
require_once(dirname(__FILE__) . "/IValidator.php");
require_once(dirname(__FILE__) . "/validators.php");

class ValidatorFactory {
    private $_validators = array();
    
    public function __construct() {
        $coreValidators = array(
            "required" => new RequiredValidator(),
            "compare" => new CompareValidator(),
            "email" => new EmailValidator(),
            "length" => new LengthValidator(),
            "integer" => new IntegerValidator()
        );
        
        foreach($coreValidators as $name=>$validator) {
            $this->register($name, $validator);
        }
    }   
    
    public function register($name, $validator) {
        if (!($validator instanceof IValidator) && !is_callable($validator))
            throw new InvalidArgumentException("\$validator must be a valid Validator class instance or a callable function which returns a Validator instance");
        $this->_validators[$name] = $validator;
    }
    
    public function resolve($name) {
        if (!isset($this->_validators[$name]))
            throw new InvalidArgumentException("'$name' is not a registered validator");
        
        $validator = $this->_validators[$name];
        if ($validator instanceof IValidator)
            return $validator;
        else {
            $validator = $validator();
            if (!($validator instanceof IValidator))
                throw new RuntimeException("'$name' did not resolve to a valid Validator instance");
            return ($this->_validators[$name] = $validator);
        }
    }
};