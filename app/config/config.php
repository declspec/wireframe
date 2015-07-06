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
        "connectionString"  => $secret["db"]["connectionString"],
        "username"          => $secret["db"]["username"],
        "password"          => $secret["db"]["password"]
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
