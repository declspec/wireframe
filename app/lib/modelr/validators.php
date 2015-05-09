<?php
// Required Validator
class RequiredValidator implements IValidator {
    private static $ERROR_FORMAT = "{field} is a required field; please specify it.";

    public function validate($model, $field, $value, $args) {
        if (empty($value)) {
            $model->addError($field, self::$ERROR_FORMAT);
            return false;
        }
        return true;
    }
};

// Integer Validator
class IntegerValidator implements IValidator {
    private static $ERROR_FORMAT = "{field} must be a valid integer.";

    public function validate($model, $field, $value, $args) {
        if (!empty($value) && preg_match('/^\d+$/', $value) === 0) {
            $model->addError($field, self::$ERROR_FORMAT);
            return false;
        }
        return true;
    }
};

// Email Validator
class EmailValidator implements IValidator {
    private static $ERROR_FORMAT = "{field} is not valid email address; please enter a valid email.";
    private static $EMAIL_TEST = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:(?:[a-z0-9!#$%&\'.*+\/=?^_`{|}~-]|\\@)+)*@[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/i';

    public function validate($model, $field, $value, $args) {
        if (!empty($value) && preg_match(self::$EMAIL_TEST, $value) !== 1) {
            $model->addError($field, self::$ERROR_FORMAT);
            return false;
        }
        return true;
    }
};

// Compare Validator
class CompareValidator implements IValidator {
    private static $ERROR_FORMAT = "{field} does not match {altfield}.";
    private static $EMAIL_TEST = "/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:(?:[a-z0-9!#$%&'.*+\/=?^_`{|}~-]|\\@)+)*@[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,}$/i";

    public function validate($model, $field, $value, $args) {
        if ($args === null || !isset($args["match"]))
            throw new InvalidArgumentException("Must specify 'match' field in the validator arguments.");
        
        $match = $args["match"];
        $valid = true;
        
        if ($value !== null && $model->get($match) !== $value) {
            $model->addError($field, self::$ERROR_FORMAT, array("altfield"=>$match));
            $valid = false;
        }
        return $valid;
    }
};

// Length Validator
class LengthValidator implements IValidator {
    private static $MAX_LENGTH_ERROR = "{field} must be no longer than {max} characters.";
    private static $MIN_LENGTH_ERROR = "{field} must be at least {min} characters.";
    
    public function validate($model, $field, $value, $args) {
        if ($args === null) 
            throw new InvalidArgumentException("Must specify at least one of 'min' or 'max' in validator arguments.");
               
        $min = isset($args["min"]) ? $args["min"] : null;
        $max = isset($args["max"]) ? $args["max"] : null;
        
        if ($min === null && $max === null)
            throw new InvalidArgumentException("Must specify at least one of 'min' or 'max' in validator arguments.");
            
        if ($value === null)
            return true;
        else if (!is_string($value))
            return false;
        else {
            $len = mb_strlen($value);
            $tokens = array("min" => $min, "max" => $max);
            $valid = false;
            
            if ($max !== null && $len > $max)
                $model->addError($field, self::$MAX_LENGTH_ERROR, $tokens);
            else if ($min !== null && $len < $min)
                $model->addError($field, self::$MIN_LENGTH_ERROR, $tokens);
            else
                $valid = true;
                
            return $valid;
        }
    }
};
?>