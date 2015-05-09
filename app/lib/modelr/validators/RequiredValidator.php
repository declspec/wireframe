<?php
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