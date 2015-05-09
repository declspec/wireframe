<?php
require './app/application.php';

// Translate json POST requests into a format that PHP understands
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') === 0 && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json = file_get_contents('php://input');
    $_POST = empty($json) ? array() : json_decode($json, true);
}

// Build the application
$config = require('./app/config/config.php');
$app = new Application($config);

// Enable logging if debugging
if ($app->get("DEBUG") === 0)
    $app->run();
else {
    $__start = microtime(true);
    $app->run();
    $__total = (microtime(true) - $__start) * 1000;
    
    $responseType = array_filter(headers_list(), function($h) {
        return preg_match('/^Content-Type:/i', $h) === 1;
    });
    
    if (count($responseType) === 0 || false !== strpos(current($responseType), "text/html")) {
        // Only output runtime on html responses
        echo PHP_EOL, "<!-- This script took ", round($__total, 3), "ms to generate -->";
    }
}
?>