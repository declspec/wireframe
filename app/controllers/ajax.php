<?php
abstract class AjaxController {
    public function __construct() {
    
    }
    
    protected function ajaxify($success, $data=null,$errors=array()) {
        header("Content-Type: application/json");
        echo json_encode(array(
            "success" => !!$success,
            "data" => $data,
            "errors" => $errors
        ));
    }
};
