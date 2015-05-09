<?php
require_once("auth.php");

class GuestPolicy extends AuthPolicy {
    public function __construct() {
        parent::__construct("auth", null);
    }

    public function run($app, $params) {
        return $app->user()->isAuthenticated()
            ? $this->unauthorized($app, "Active session found; please logout to continue.")
            : true;
    }
};