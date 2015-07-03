<?php
// Create any sensitive information (i.e. passwords etc. here that you don't want to show in
// the public config.php file. You can reference this array from config.php. Users can then set
// up their own copy to match their own environment

return array(
    "db" => array(
        "connectionString"  => "mysql:host=localhost;dbname=example_mysql",
        "username"          => "user",
        "password"          => "***"
    )
);
