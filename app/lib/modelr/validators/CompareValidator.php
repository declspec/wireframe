<?php
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
?>