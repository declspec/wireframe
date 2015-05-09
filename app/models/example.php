<?php
require_once(__DIR__ . "/../lib/modelr/Model.php");
require_once(__DIR__ . "/../lib/modelr/Schema.php");

class ExampleModel extends Model {
    protected static $schema = null;
    
    protected function getSchema() {
        if (self::$schema === null) {
            self::$schema = Schema::compile(array(
                "fields" => array(
                    "summoner" => "Summoner name",
                    "password" => "Password",
                    "friendly" => "Display name",
                    "repeatPassword" => "Repeated password",
                    "email" => "Email address",
                    "region" => "Region"
                ),
                "rules" => array(
                    array(
                        "validator" => "required",
                        "fields" => array("summoner", "password", "repeatPassword", "email", "friendly", "region")
                    ),
                    array(
                        "validator" => "email",
                        "fields" => "email"
                    ),
                    array(
                        "validator" => "length",
                        "fields" => "password",
                        "args" => array("min" => 6)
                    ),
                    array(
                        "validator" => "compare",
                        "fields" => "repeatPassword",
                        "args" => array("match" => "password")
                    )
                )
            ));
        }
        return self::$schema;
    }
};
?>