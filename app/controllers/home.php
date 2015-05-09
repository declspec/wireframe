<?php
class HomeController {
    public function __construct() {
    }   
    
    public function index($app) {
        $app->renderer()->render("home/index");
    }
};