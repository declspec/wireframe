<?php
class AdminPolicy extends Policy {
    public function __construct() {
        parent::__construct("admin", "auth");
    }

    public function run($app, $params) {
        if (!$app->user()->hasRole("admin")) {
            $app->error(403);
            return false;
        }
        return true;
    }
}