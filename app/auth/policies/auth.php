<?php
abstract class AuthPolicy extends Policy {
    const DEFAULT_UNAUTHORIZED_MESSAGE = "You are not authorized to make this request.";
    
    public function __construct($name, $chain) {
        parent::__construct($name, $chain);
    }
    
    protected function unauthorized($app, $message=null, $status=401) {
        if ($message === null)
            $message = array(self::DEFAULT_UNAUTHORIZED_MESSAGE);
        else if (!is_array($message))
            $message = (array)$message;
            
        $app->status($status);
        echo json_encode(array("success" => false, "data" => null, "errors" => $message));
        return false;
    }
};