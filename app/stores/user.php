<?php
require_once("sqlstore.php");

class UserStore extends SqlStore {
    public function __construct($userMapper, $db) {
        parent::__construct($userMapper, $db);
    }
    
    public function findByEmail($email) {
        return $this->findOneWhere("LOWER(email) = :email", array(":email" => $email));
    }
};
