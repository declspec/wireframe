<?php
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