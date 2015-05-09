<?php
return array(
    "defaultController" => "home",
    "autoEscapeHtml" => FALSE,
    "debugLevel" => 2,
    
    // configuration items that are accessible anywhere in the application
    "settings" => array(
    ),

    "routes" => array(
        "GET /" => array()
    ),
    
    // Core config
    "db" => array(
        "connectionString"  => "mysql:host=localhost;dbname=example_mysql",
        "username"          => "user",
        "password"          => "***"
    ),
    "session" => array(
        "timeout" => 20,
        "autoExpire" => true
    ),
    "modules" => array(
        "core",
        "controllers",
        "routes"
    ),
);