<?php
interface IValidator {
    public function validate($model, $field, $value, $args);
};
?>