<?php
require_once("mapper.php");

class UserMapper extends SqlDataMapper {
    public function __construct() {
        parent::__construct("user", "id", true, array(
            "id" => "id",
            "email" => "email",
            "friendly" => "friendly",
            "password" => "password",
            "status_id" => "statusId",
            "token" => "token",
            "date_created" => "dateCreated",
            "date_modified" => "dateModified"
        ));
    }
};