<?php
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